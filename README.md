# fresh_paw_service
Service that sends new PAW for old ones and helps retain a persons anonymity.

## DB
Import paw.sql to your MySQL DB

## Domain 
Set up a new domain on your webserver and point it to the /public folder.
Create a SSL certificates for your domain ( e.g. with with certbot: certbot certonly --nginx -d freshpawservice.com ).

## Setup
Copy config.php.sample to config.php
Fill out all settings config.php

## Set a schedule
Copy cron.sh.sample to cron.sh
Update the domain in the cron.sh file
Add execution rights to the file with chmod +x cron.sh
Add a one minute interval call on the script file with crontab -e by adding this line:
```
* * * * * /var/www/html/fresh_paw_service/cron.sh
```



### Sample nginx file using SSL
```
server {
    listen 80;
    server_name freshpawservice.com;
    return 301 https://$host$request_uri;
}
server {
	server_name freshpawservice.com;
	listen 443 ssl;

	# managed by Certbot
	ssl_certificate /etc/letsencrypt/live/freshpawservice.com/fullchain.pem;
	ssl_certificate_key /etc/letsencrypt/live/freshpawservice.com/privkey.pem;
	include /etc/letsencrypt/options-ssl-nginx.conf;
	ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

	root /var/www/html/fresh_paw_service/public;
	index index.php index.html index.htm index.nginx-debian.html;

	location / {
		# try to serve file directly, fallback to app.php
		try_files $uri /index.php$is_args$args;
	}


	# pass PHP scripts to FastCGI server
	location ~ \.php$ {
		include snippets/fastcgi-php.conf;
		
		fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
		fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
		fastcgi_param DOCUMENT_ROOT $realpath_root;

		internal;
	}
}
```
