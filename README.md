# VimeoChallenge RESTful API v0.9
This is a simple API that includes a datadump of a million records.
Requirements: PHP 7, MySQL, Apache Server

-Added Composer psr-4 Autoload
-Cleaned up code

##UBUNTU INSTRUCTIONS
------------------------------------------------------------------------------

1. ###Install Apache
- sudo apt-get install apache2

2. ###Install PHP and Libraries
- sudo apt-get install libapache2-mod-php7.0 php7.0-mysql php7.0-curl php7.0-json

3. ###Install MySQL and Basic Setup
- sudo apt-get install mysql-server
- mysql -u root -p
- create database vimeochallenge;
- create user 'vimeo'@'localhost' identified by 'password';
- GRANT ALL PRIVILEGES ON vimeochallenge.* TO 'vimeo'@'localhost';
- FLUSH PRIVILEGES;
- exit;

4. ###Upload Files and Modify config.php
- Upload your files into the default /var/www/html directory
- Modify config.php file with your DB settings

5. ###Run Data Dumper
- Run dump.php that is locatted inside the DUMPER directory
- php /var/www/html/dumper/dump.php
- This will take provided dump file and read each line.  It will add each record to it's corresponding DB table.
Country table will be also be created as well as the totals will be tallied at the end.

6. ###Enable Mod Rewrite
- sudo a2enmod rewrite

7. ###Modify apache2.conf
-Modify the following:
`<Directory />`
	Options All
	AllowOverride All
	Require all denied
`</Directory>`

`<Directory /usr/share>`
	AllowOverride None
	Require all granted
`</Directory>`

`<Directory /var/www/>`
	Options Indexes FollowSymLinks
	AllowOverride All
	Require all granted
`</Directory>`

8. ###Restart Apache Server
- sudo service apache2 restart

Go to http://docs.vimeochallengeapi.apiary.io - automatic!
[http://docs.vimeochallengeapi.apiary.io](http://docs.vimeochallengeapi.apiary.io) and read documentation on the available endpoints.  You can also test the API with a mock server.
I also used POSTMAN to test the api’s locally
