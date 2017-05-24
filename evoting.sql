CREATE DATABASE evoting;

CREATE TABLE voter
(
username varchar (255) NOT NULL PRIMARY KEY,
email varchar(255) NOT NULL,
status tinyint(1) default 0,
password varchar(255) NOT NULL
);


CREATE TABLE votes
(
pkid int(20) NOT NULL PRIMARY KEY,
base int(255) NOT NULL,
election_status tinyint(1) default 0,
total_votes varchar(4000) default 1,
key_pair varchar (4000)
);

CREATE TABLE evoting.candidate ( name VARCHAR(255) NOT NULL , age INT(50) NOT NULL , description VARCHAR(255) NOT NULL , votes INT(255) NOT NULL ) ENGINE = InnoDB;


INSERT INTO votes VALUES (1141124431,10,1,'0','0');

INSERT INTO `candidate`(`name`, `age`, `description`, `votes`) VALUES ('Trump',70,'Allegedly, He has some times no respect for women',0);
INSERT INTO `candidate`(`name`, `age`, `description`, `votes`) VALUES ('Hillary',69,'She Was once General secretary of United states',0);
INSERT INTO `candidate`(`name`, `age`, `description`, `votes`) VALUES ('Obama',70,'Yes We can',0);
INSERT INTO `candidate`(`name`, `age`, `description`, `votes`) VALUES ('None',0,'total votes abstained voters',0);