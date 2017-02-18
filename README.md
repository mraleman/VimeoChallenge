# VimeoChallenge RESTful API

UBUNTU INSTRUCTIONS
------------------------------------------------------------------------------

**Install Apache**
- sudo apt-get install apache2

**Install PHP and Libraries**
- sudo apt-get install libapache2-mod-php7.0 php7.0-mysql php7.0-curl php7.0-json

**Install MySQL and Basic Setup**
- sudo apt-get install mysql-server
- mysql -u root -p
- create database vimeochallenge;
- create user 'vimeo'@'localhost' identified by 'password';
- GRANT ALL PRIVILEGES ON vimeochallenge.* TO 'vimeo'@'localhost';
- FLUSH PRIVILEGES;
- exit;

**Upload Files and Modify config.php**
- Upload your files into the default /var/www/html directory
- Modify config.php file with your DB settings

**Run Data Dumper**
- Run dump.php
- php /var/www/html/dump.php
- This will take provided dump file and read each line.  It will add each record to the corresponding DB table and also calculate grab the totals 

**Enable Mod Rewrite**
- sudo a2enmod rewrite

**Restart Apache Server**
- sudo service apache2 restart

Go to http://docs.vimeochallengeapi.apiary.io and read documentation on the available endpoints.  You can also test the API with a mock server.
I also used POSTMAN to test the api’s locally
