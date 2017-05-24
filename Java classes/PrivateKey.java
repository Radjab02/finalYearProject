/*
 *Private Key class that hold the private Key
 */
package pailliercrypto;
import java.math.BigInteger;

/**
 * This a class for private key
 * the instance of this class holds u and lambda which are of type BigInteger
 * @author Raj
 */
class PrivateKey {
      private final BigInteger lambda;
    private final BigInteger u;
	
	
	PrivateKey(BigInteger lambda, BigInteger u) {
        this.lambda = lambda;
        this.u = u;
    }
	
	
	BigInteger get_lambda() {
        return lambda;
    }
	
	 BigInteger get_u() {
        return u;
    }
}
