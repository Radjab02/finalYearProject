/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package pailliercrypto;
import java.math.BigInteger;

/**
 * This a class for public key
 * the instance of this class holds n and g which are of type BigInteger
 * @author Raj
 */
class PublicKey {
    
    private final BigInteger n;
    private final BigInteger g;
	
	
	PublicKey(BigInteger n, BigInteger g) {
        this.n = n;
        this.g = g;
    }
	
	
	BigInteger get_n() {
        return n;
    }
	
	 BigInteger get_g() {
        return g;
    }
    
}
