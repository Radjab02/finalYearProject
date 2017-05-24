
package pailliercrypto;

/**
 *The PaillierKeyPair class allows us to create an object that holds a pair of keys: public and private keys. 
 * inside this class  two private fields of type PrivateKey and PublicKey classes are created.
 * @author Raj
 */
public class PaillierKeyPair {
    private final  PrivateKey privateKey;
    private final  PublicKey publicKey;
	
    /**
     *
     * @param publicKey hold the public key parameters n and g
     * @param privateKey hold the private key parameters u and lambda
     */
    PaillierKeyPair(PublicKey publicKey, PrivateKey privateKey) {
        this.publicKey = publicKey;
        this.privateKey = privateKey;
    }

    PaillierKeyPair() {
        throw new UnsupportedOperationException("Not supported yet."); //To change body of generated methods, choose Tools | Templates.
    }

     /* returns the public key object */
	 PublicKey getPublicKey() {
        return publicKey;
    }
	/* returns the private key object */
	 PrivateKey getPrivateKey() {
         return privateKey;
    }

}
