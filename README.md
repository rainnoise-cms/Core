# rainnoise-cms
Проект в разработке. Документация будет доступна позже

Требования:   
- PHP >= v7.3   
- MySql >= v5.8   

Сервер должен быть настроен так, чтобы папка `www` проекта была корневой.   
При необходимости отредактируйте `httpd.conf`   

Для развертывания выполните:   
`$ git clone https://github.com/rainnoise-cms/Core /path/to/server/dir`  
`$ cd /path/to/server/dir/www`  
`$ composer install`  


### Модули  
Развертываются в папку `/www/Modules/[module_name]`  
Page - https://github.com/RainNoise/rcms-page  
Decorator - https://github.com/RainNoise/rcms-decorator
