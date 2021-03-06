#!/bin/sh
echo 正在停止服务
service nginx stop

echo 下载依赖程序,y/n?
read choose
if [ "${choose}" = "y" ]; then
apt install zip unzip curl nginx php7.0 php7.0-fpm mysql-server composer php7.0-mbstring php7.0-xml php7.0-mysql
curl -sL https://deb.nodesource.com/setup_8.x | sudo -E bash -
sudo apt install -y nodejs
fi

echo "======生成前端====>y/n?"
read choose
if [ "${choose}" = "y" ]; then
cd front
npm install
npm run build
cp dist/index.html ../php/public/index.html
cp -R dist/static ../php/public/
cd ..
fi

echo "======正在处理后端====>"
cd php

echo 正在初始化Laravel
php artisan key:generate
php artisan storage:link

chown -R www-data:www-data storage/
chmod -R 777 ./storage
chmod -R 777 ./bootstrap/cache
chmod 777 ./public/index.html

echo 更新composer,y/n?
read choose
if [ "${choose}" = "y" ]; then
composer update
fi

echo 创建数据库,y/n?
read createdb
if [ "${createdb}" = "y" ]; then
	echo 请输入数据库名称：
	read dbname
	echo 请输入数据库用户名：
	read dbuser
	echo 请输入数据库密码：
	read dbpwd

	echo 备份.env为.env.bk
	cp .env .env.bk

	echo 设置数据库参数
	sed -i "s/DB_DATABASE=.*/DB_DATABASE=${dbname}/" .env
	sed -i "s/DB_USERNAME=.*/DB_USERNAME=${dbuser}/" .env
	sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=${dbpwd}/" .env

	MYSQL_CMD="mysql -u${dbuser} -p${dbpwd}"
	create_db_sql="create database IF NOT EXISTS ${dbname} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
	echo ${create_db_sql}  | ${MYSQL_CMD}
	if [ $? -ne 0 ]; then
		echo 创建数据库失败，请确认mysql用户密码，或者尝试修改本脚本，为mysql设置正确的host和端口
		exit
	fi
fi

echo 正在更新数据表
php artisan migrate

echo 正在新建API客户端
php artisan passport:keys
chown -R www-data:www-data storage/
chmod -R 777 ./storage
php artisan passport:client --password
php artisan initblog_setapipassword

echo 基础初始化已完成
echo 设置新的拥有者，y/n?
read donew
if [ "${donew}" = "y" ]; then
	echo 请输入拥有者的Email（作为登陆名）:
	read email
	echo 请输入拥有者的昵称:
	read nick
	echo 请输入拥有者的密码:
	read pwd
	echo 正在设置拥有者
	php artisan initblog $email $nick $pwd
	echo 设置拥有者完成
fi

cd ..

echo 正在重启
service php7.0-fpm restart
service nginx start
#nginx -s reload
echo 初始化全部完成。Enjoy！
