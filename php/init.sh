#!/bin/sh
#chown -R :www-data ./
echo 正在下载依赖程序
apt install zip unzip nginx php7.0 mysql-server composer php7.0-mbstring php7.0-dom php7.0-mysql
echo 正在设置文件系统权限
chmod -R 775 ./storage
chmod -R 775 ./bootstrap/cache

echo 正在更新必要的模块
composer update

echo 正在初始化Laravel
php artisan key:generate
php artisan storage:link

echo 开始初始化数据库
echo 请输入数据库名称：
read dbname
echo 请输入数据库用户名：
read dbuser
echo 请输入数据库密码：
read dbpwd

echo 备份.env为.env.bk
cp .env .env.bk

echo 设置数据库参数
sed -i "s/DB_DATABASE=(.*)/DB_DATABASE=$dbname/" .env
sed -i "s/DB_USERNAME=(.*)/DB_USERNAME=$dbuser/" .env
sed -i "s/DB_PASSWORD=(.*)/DB_PASSWORD=$dbpwd/" .env

echo 正在创建数据库
MYSQL_CMD="mysql -u${dbuser} -p${dbpwd}"
create_db_sql="create database IF NOT EXISTS ${dbname} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
echo ${create_db_sql}  | ${MYSQL_CMD}
if [ $? -ne 0 ]; then
	echo 创建数据库失败，请确认mysql用户密码，或者尝试修改本脚本，为mysql设置正确的host和端口
	exit
fi
echo 正在更新数据表
php artisan migrate

echo 正在新建API客户端
php artisan passport:client --password
php artisan initblog_setapipassword

echo 基础初始化已完成
echo 设置新的拥有者，y/n?
read donew
if [ $donew == "y" ]
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

echo 初始化全部完成。Enjoy！
