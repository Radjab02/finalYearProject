
This project was created in order to prevent cheating that usually happens during counting votes because votes are decrypted prior to be tallied. The security comes from the fact that the browser encrypts the vote prior to sending the vote to the server which performs tallying on those encrypted votes without decrypting them. The e-voting consists of index.php, login.php, registration file(reg2.php) and profile, and logout.php files. Assuming you have NetBeans and Xampp installed. Here is how to run this project.

Tools required:
   1 Netbeans IDE
   2 Xampp 

First of all make sure  that you have created the database and tables before following this guide. (to create database tables, refer to evoting.sql file)	   
 
In NetBens
1. create a java application and copy the java files to the src folder of your application. 
2. Copy web files and paste them in htdocs under xampp folder.  Go to NetBeans and press Crlt+Shift+O on your keyboard and choose the evoting folder and open it.
3. Right click on the evoting folder inside NetBeans and choose properties to verify that the path is correct. (optional)

In XAMPP

4. Configuring ports in Xampp control panel:
   Change the localhost port to 8080 inside the apache (httpd.config) file as follows:     Listen:8080     ServerName localhost:8080
5. Download the javaBridge.ar file from  http://sourceforge.net/projects/php-java-bridge/files/Binary%20package/php-java-bridge_7.1.3/exploded/JavaBridge.jar/download  and  Add the javabridge.jar  in Netbeans by right clicking libraries and choose Add JAR file you just downloaded

6. To run the web application you need to start Apache and MySQL

7. In NetBeans Select PaillierCrypto project and  run it.  Check Xampp control panel , Tomcat server should be running on port 8080

8. Go to the evoting project inside Netbeans, under source files double click on index.php and hit run again.

 a browser should open and load localhost:8080/evoting/index.php

