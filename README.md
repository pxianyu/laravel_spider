用laravel console实现了一个简单的爬虫，爬取laravel-china的文档,并写成md文档
# 安装方式
    composer intsall
## 使用方式
    php artisan spider url地址
### 例
    php artisan spider https://learnku.com/docs/laravel/9.x/releases/12197
## 爬取结果
    storage/app/public/文档标题/小标题.md

如果需要使用cookie，可以在app\Console\Spider.php中给$client设置cookie;