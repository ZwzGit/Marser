phalcon快速开发基础框架
=================

#### nginx配置
```
server {
				listen 80;
				server_name test.com;
				root /path/Marser/public;
				index index.php index.html index.htm;

				location / {
								if ($request_uri ~ (.+?\.php)(|/.+)$ ) {
												break;
								}

								if (!-e $request_filename) {
												rewrite ^/(.*)$ /index.php?_url=/$1;
								}

				}

				location ~ \.php {
						fastcgi_pass  unix:/tmp/php-cgi.sock;
						fastcgi_index index.php;
						include fastcgi_params;
						set $real_script_name $fastcgi_script_name;
						if ($fastcgi_script_name ~ "^(.+?\.php)(/.+)$") {
								set $real_script_name $1;
								set $path_info $2;
						}
						fastcgi_param SCRIPT_FILENAME $document_root$real_script_name;
						fastcgi_param SCRIPT_NAME $real_script_name;
						fastcgi_param PATH_INFO $path_info;
				}

				access_log  /path/logs/Marser/access.log  access;
				error_log  /path/logs/Marser/error.log;
}
```
