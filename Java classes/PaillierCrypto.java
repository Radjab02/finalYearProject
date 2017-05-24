/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * @author: Harerimana Radjab
 */
package pailliercrypto;

import java.io.*;
import java.math.*;
import static java.math.BigInteger.valueOf;
import java.security.SecureRandom;

/**
 * This library implements the Paillier's encryption scheme which supports
 * additive homomorphic property. The homomorphic property works as follows:
 * Given the public key and the ciphertext C1 =Enc(M1 ), C2=Enc(M2), one can
 * compute C3=C1*C2 and decrypt the plaintext as M3=M1 + M2, without knowing
 * what is the value of M1 and M2.
 *
 * @author Raj
 */
public final class PaillierCrypto {

    
    public static final String JAVABRIDGE_PORT="8080";
     static final php.java.bridge.JavaBridgeRunner runner = 
     php.java.bridge.JavaBridgeRunner.getInstance(JAVABRIDGE_PORT);
    
    
    /**
     * p and q are two large primes. lambda = lcm(p-1, q-1) =
     * (p-1)*(q-1)/gcd(p-1, q-1).
     */
    private BigInteger p, q, lambda, u;
    /**
     * n = p*q, where p and q are two large primes.
     */
    public BigInteger n;
    /**
     * nsquare = n*n
     */
    public BigInteger nsquare;
    /**
     * a random integer in Z*_{n^2} where gcd (L(g^lambda mod n^2), n) = 1.
     */
    private BigInteger g;
    /**
     * number of bits of modulus
     */
    private int modBitLength;
    /*
    *  Secure random object 
     */
    SecureRandom sr;
    
    public PaillierCrypto() {
       //this will be used by client side
     //  PrivateKey privKey = readPrivateKeyFromFile();
      // PublicKey pubKey = readPublicKeyFromFile(fileName);
    }

    /**
     * the keys used for encryption are initialized in the
     * Constructor 
     * @param bitLengthVal
     * @param certainty
     * @throws IOException
     */
    
    public PaillierCrypto(int bitLengthVal, int certainty) throws IOException {

        PaillierKeyPair keys = KeyGeneration(bitLengthVal, certainty);
        savePrivateKey("PrivateKey.key", keys.getPrivateKey());
        savePublicKey("PublicKey.key", keys.getPublicKey());
    }
//    public PublicKey get_Public_key(PaillierKeyPair keys){
//           return keys.getPublicKey();
//    }
//    public PrivateKey get_Private_key(PaillierKeyPair keys){
//           return keys.getPrivateKey();
//    }
    
    /**
     * generates PaillierKeyPair object which holds public key and private key.
     * rejects bit length which is less than 1024 bits generates strong prime p
     * and q, set values for n, nsquare,g,u and lambda verifies g, by computing
     * gcd(L(g^lambda mod n^2), n) = 1, where L(u) = (u-1)/n     *     
     * @param modBitLenVal number of bits of modulus.
     * @param certainty probability that the new BigInteger represents a prime
     * number will exceed (1 - 2^(-certainty)). affect execution time generates
     * public key and private key.
     * @return PaillierKeyPair object which holds public key and private key.
     */
    
    public PaillierKeyPair KeyGeneration(int modBitLenVal, int certainty) {

        if (modBitLenVal < 1024) {
            System.out.println("Paillier(int modLength): modLength must be >= 1024");
            System.exit(1);
        }
        modBitLength = modBitLenVal;

        p = getStrongPrime();
        // q = new BigInteger(modBitLength / 2, certainty, new SecureRandom());

        do {
            q = getStrongPrime();
            //q = new BigInteger(modBitLength / 2, certainty, new SecureRandom());
        } while (q.compareTo(p) == 0);

        n = p.multiply(q);
        nsquare = n.multiply(n);

        /* lambda =(p-1)(q-1)/(gcd(p-1),(q-1)) */
        lambda = p.subtract(BigInteger.ONE).multiply(q.subtract(BigInteger.ONE)).divide(
                p.subtract(BigInteger.ONE).gcd(q.subtract(BigInteger.ONE)));
        /* check whether g is good. ie. gcd L(g^λ mod n^2), n ) = 1 */
        do {

            g = randomNumFromZStarNSquare();
        } // verify g, by computing gcd(L(g^lambda mod n^2), n) = 1, where L(u) = (u-1)/n
        while (g.modPow(lambda, nsquare).subtract(BigInteger.ONE).divide(n).gcd(n).intValue() != 1);

        u = g.modPow(lambda, nsquare).subtract(BigInteger.ONE).divide(n).modInverse(n);

        PublicKey publicKey = new PublicKey(n, g);
        PrivateKey privateKey = new PrivateKey(lambda, u);
        PaillierKeyPair keyPair = new PaillierKeyPair(publicKey, privateKey);

        return keyPair;
    }

    /**
     * Encrypts plaintext m. Ciphertext c = g^m * r^n mod n^2. Generate r using
     * function randomNumFromZN() for semantic security
     *
     * @param message ciphertext as a byte array
     * @param fileName which contains the name of the file that contain the
     * public key The public key is retrieved from the file with the function
     * readPrivateKeyFromFile
     * @return ciphertext as a Byte array
     * @throws java.io.IOException in case there is failure to read/write the
     * key from the file.
     */
    public BigInteger Encryption(BigInteger message, String fileName) throws IOException {
      //  BigInteger encryptedData, m;
        //byte[] ciphertextInBytes;
        
        // This avoids getting negative numbers when the first bit of the byte sequence is a negative sign.
      //  m = new BigInteger(1,message); 
        PublicKey pubKey = readPublicKeyFromFile(fileName);
        g = pubKey.get_g();
        n = pubKey.get_n();
        nsquare = n.multiply(n);
        BigInteger r = randomNumFromZN();
       // encryptedData = g.modPow(m, nsquare).multiply(r.modPow(n, nsquare)).mod(nsquare);
       // ciphertextInBytes = encryptedData.toByteArray();
        //return ciphertextInBytes;
        return g.modPow(message, nsquare).multiply(r.modPow(n, nsquare)).mod(nsquare);
    }

    /**
     * Decrypts ciphertext c. plaintext m = L(c^lambda mod n^2) * u mod n, where
     * u = (L(g^lambda mod n^2))^(-1) mod n.
     *
     * @param ciphertext ciphertext as a BigInteger
     * @param fileName which contains the name of the file that contain the
     * private key the private key is retrieved from the file with the function
     * readPrivateKeyFromFile
     * @return plaintext as a Byte array
     * @throws java.io.IOException in case there is failure to read/write the
     * key from the file.
     */
    public BigInteger Decryption( BigInteger  ciphertext, String fileName) throws IOException {

        //BigInteger c, plaintext;
       // c = new BigInteger(1,ciphertext);
      //  byte[] plaintextInBytes;

        PrivateKey privKey = readPrivateKeyFromFile(fileName);
        //PublicKey pubKey = readPublicKeyFromFile(fileName);  
        lambda = privKey.get_lambda();
        u = privKey.get_u();
       // plaintext = c.modPow(lambda, nsquare).subtract(BigInteger.ONE).divide(n).multiply(u).mod(n);
        //plaintextInBytes = plaintext.toByteArray();
        //return plaintextInBytes;
        return ciphertext.modPow(lambda, nsquare).subtract(BigInteger.ONE).divide(n).multiply(u).mod(n);
    }

    /**
     * getStrongPrime generates a strong prime as following: The strong prime p
     * will be such that p+1 has a large prime factor s p-1 will have a large
     * prime factor r. p+1 has a large prime factor s. r-1 has a large prime
     * factor t. so r is the first prime in the sequence 2t+1, 2*2t+1, 2*3t+1,
     * The strong prime p is the first prime in the sequence 2rs+p*, 2*2rs+p*,
     * 2*3rs+p*,...
     *
     * @return a BigIntger strong prime
     * @author:David Bishop URL:
     * <a href="https://books.google.com.my/books/about/Introduction_to_Cryptography_with_Java_A.html?id=yxPnt4S3mFMC&redir_esc=y">https://books.google.com.my/books/about/Introduction</a><br>
     */
    public BigInteger getStrongPrime() {
        sr = new SecureRandom();
        //The strong prime p will be such that p+1 has a large prime factor s        
        BigInteger s = new BigInteger(modBitLength / 2 - 8, modBitLength, sr);
        //t will be a large prime factor of r, which follows
        BigInteger t = new BigInteger(modBitLength / 2 - 8, modBitLength, sr);
        BigInteger i = BigInteger.valueOf(1);
        //p-1 will have a large prime factor 
        //r is the first prime in the sequence 2t+1, 2*2t+1, 2*3t+1,...
        BigInteger r;
        BigInteger _TWO = valueOf(2);
        do {
            r = _TWO.multiply(i).multiply(t).add(BigInteger.ONE);
            i = i.add(BigInteger.ONE);
        } while (!r.isProbablePrime(modBitLength));
        BigInteger z = s.modPow(r.subtract(_TWO), r);//modular inverse
        BigInteger pstar = _TWO.multiply(z).multiply(s).subtract(BigInteger.ONE);
        BigInteger k = BigInteger.valueOf(1);
        //The strong prime p is the first prime in the sequence 2rs+p*, 2*2rs+p*, 2*3rs+p*,...
        BigInteger _p = _TWO.multiply(r).multiply(s).add(pstar);
        while (_p.bitLength() <= modBitLength) {
            k = k.multiply(_TWO);
            _p = _TWO.multiply(k).multiply(r).multiply(s).add(pstar);
        }
        while (!_p.isProbablePrime(modBitLength)) {
            k = k.add(BigInteger.ONE);
            _p = _TWO.multiply(k).multiply(r).multiply(s).add(pstar);
        }
        return _p;
    }

    /**
     * randomNumFromZstarnNsquare requires no input parameter(s). the function
     * uses secureRandom class this function checks whether the random number
     * generated belongs to z*{n}
     *
     * @return a random BigIntger r that belongs in Z*_{n}
     */
    public BigInteger randomNumFromZN() {
        BigInteger r;

        do {
            r = new BigInteger(modBitLength, new SecureRandom());
        } while (r.compareTo(BigInteger.ZERO) <= 0 || r.compareTo(n) >= 0); // r belongs to z*{n}

        return r;
    }

    /**
     * randomNumFromZstarnNsquare requires no input parameter(s). the function
     * uses secureRandom class It also checks whether the random number
     * generated is not equal to nsquare and and It checks that r does not have
     * common factor with nsquare.
     *
     * @return a random BigIntger r which belongs in Z*_{n^2}
     */
    public BigInteger randomNumFromZStarNSquare() {
        BigInteger r;

        do {
            r = new BigInteger(modBitLength * 2, new SecureRandom());
        } while (r.compareTo(nsquare) >= 0 || r.gcd(nsquare).intValue() != 1); //check if g is relative prime to nsquare

        return r;
    }

    // save function should zip to decrease the file size
    // need to  import a class
    private void savePublicKey(String fileName, PublicKey pubKey) throws IOException {

        BigInteger _g = pubKey.get_g();
        BigInteger _n = pubKey.get_n();
        FileOutputStream fos = null;
        ObjectOutputStream oos = null;

        try {
            System.out.println("Generating " + fileName + " ... ");

            fos = new FileOutputStream(fileName);
            oos = new ObjectOutputStream(new BufferedOutputStream(fos));
            oos.writeObject(_n);
            oos.writeObject(_g);

            System.out.println(fileName + " generated successfully");

        } catch (Exception e) {
            e.getMessage();
        } finally {

            if (oos != null) {
                oos.close();
            }
            if (fos != null) {
                fos.close();
            }
        }

    }

    // save function should zip to decrease the file size
    // should import the function that zip  the file
    private void savePrivateKey(String fileName, PrivateKey privKey) throws IOException {

        BigInteger _lambda = privKey.get_lambda();
        BigInteger _mu = privKey.get_u();
        FileOutputStream fos = null;
        ObjectOutputStream oos = null;

        try {
            System.out.println("Generating " + fileName + " ... ");

            fos = new FileOutputStream(fileName);
            oos = new ObjectOutputStream(new BufferedOutputStream(fos));
            oos.writeObject(_lambda);
            oos.writeObject(_mu);

            System.out.println(fileName + " generated successfully");

        } catch (Exception e) {
            e.getMessage();
        } finally {

            if (oos != null) {
                oos.close();
            }
            if (fos != null) {
                fos.close();
            }
        }

    }

    private PublicKey readPublicKeyFromFile(String fileName) throws IOException {
        FileInputStream fis = null;
        ObjectInputStream ois = null;
        PublicKey pubKey = null;
        try {
            fis = new FileInputStream(new File(fileName));
            ois = new ObjectInputStream(fis);
            BigInteger _n = (BigInteger) ois.readObject();
            BigInteger _g = (BigInteger) ois.readObject();
            //get the public key
            pubKey = new PublicKey(_n, _g);

        } catch (IOException | ClassNotFoundException e) {
        } finally {
            if (ois != null) {
                ois.close();
            }
            if (fis != null) {
                fis.close();
            }
        }
        return pubKey;
    }

    private PrivateKey readPrivateKeyFromFile(String fileName) throws IOException {
        FileInputStream fis = null;
        ObjectInputStream ois = null;
        PrivateKey privKey = null;
        try {
            fis = new FileInputStream(new File(fileName));
            ois = new ObjectInputStream(fis);
            BigInteger _lambda = (BigInteger) ois.readObject();
            BigInteger _mu = (BigInteger) ois.readObject();

            //Get pub key      
            privKey = new PrivateKey(_lambda, _mu);

        } catch (IOException | ClassNotFoundException e) {
            System.out.println(e);
        } finally {
            if (ois != null) {
                ois.close();
            }
            if (fis != null) {
                fis.close();
            }
        }
        return privKey;
    }
 
    /**
     * This method illustrates the additive homomorphic property i.e
     * D(E(m1)*E(m2) mod n^2) = (m1 + m2) mod n Multiplying encrypted messages
     * results in the addition of the original plaintext mod n^2 this function
     * performs multiplication on two inputs ciphertext data tye is a BigInteger
     *
     * @param c1 stands for ciphertext1
     * @param c2 stands for ciphertext2
     * @return M3, the decrypted values of the product of c1 and c2
     * @throws java.io.IOException in case there is failure to read/write the
     * key from the file.
     */
     public BigInteger multiplicationOnCiphertext(BigInteger c1, BigInteger c2) throws IOException {

        BigInteger c3 = c1.multiply(c2).mod(nsquare);
        //String m3 = new BigInteger(Decryption(c3.toByteArray(), "PrivateKey.key")).toString();
        return c3;
    }
    
    /* AdditionOnPlaintext returns a sum of two messages */
 /* the addition operating is done on unencrypted data*/
    /**
     * this function performs addition on two input messages data tye is a
     * BigInteger
     *
     * @param m1 message1
     * @param m2 message 2
     * @return m3, the sum of message1 plus message2.
     */
   /* public String additionOnPlaintext(BigInteger m1, BigInteger m2) {
        BigInteger sum = m1.add(m2).mod(n);
        return sum.toString();
    }*/

    /**
     * printVallues method prints of p,q,n,lambda,u,g and nsquare. this function
     * prints values of all parameters which is useful for testing purposes.
     * returns nothing
     */
    public void printValues() {
        System.out.println("p:       " + p);
        System.out.println("q:       " + q);
        System.out.println("lambda:  " + lambda);
        System.out.println("n:       " + n);
        System.out.println("nsquare: " + nsquare);
        System.out.println("g:       " + g);
        System.out.println("mu:      " + u);
    }
    
    public BigInteger getBigInteger ( String a ){
    BigInteger b = new BigInteger(a);
    return b; 
    }
    public String getString( BigInteger a ){
    String b  = a.toString();
    return b; 
    }


    /**
     * This is a Driver class that calls the Paillier encryptions scheme
     * library, in order to test additive homomorphic properties. Instantiates
     * an PaillierCryptosystem object to call the library. Instantiates a
     * PaillierKeyPair object that holds private and public Key. call
     * KeyGeneration method to create public and private Keys. Saves the key in
     * separate files once they are created. Encryption and decrypts messages
     * Test additive homomorphic properties by calling
     * multiplicationOnCiphertext
     *
     * @param args contains the supplied command-line arguments as an array of
     * String objects.
     * @throws java.io.IOException in case there is failure to read/write the
     * key from the file.
     */
    public static void main(String[] args) throws Exception {
        
         System.out.println("Starting...");
         System.out.println("waiting for PHP call...");
        runner.waitFor(); 
        //paillier.printValues(); 
        System.exit(0);
      
        
    }

}
