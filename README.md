使用步骤  
#安装composer   
curl -sS https://getcomposer.org/installer | php     
mv composer.phar /usr/local/bin/composer   
     
   

#下载composer_app.json文件       
ls
-rw-r--r-- 1 root root  651 4月  20 22:00 composer_app.json    
 
        
        
#安装框架，初始化app目录     
composer install   
   
           
#使用php内置server运行代码  
php -S 0.0.0.0:8812  -t public/  
     
