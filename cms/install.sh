#!/bin/bash
echo "Database Name:"
read -e dbname
echo "Database User:"
read -e dbuser
echo "Database Password:"
read -s dbpass
echo "run install? (y/n)"
read -e run
if [ "$run" == n ] ; then
exit
else
#download wordpress
curl -O http://wordpress.org/latest.tar.gz
#unzip wordpress
tar -zxvf latest.tar.gz
#change dir to wordpress
cd wordpress
#copy file to parent dir
rsync -av --exclude='wp-content' . ..

#move back to parent dir
cd ..
#remove files from wordpress folder
rm -R wordpress
#create wp config
cp wp-config-sample.php wp-config.php
#set database details with perl find and replace
sed -e "s/database_name_here/$dbname/g" wp-config.php
sed -e "s/username_here/$dbuser/g" wp-config.php
sed "s/password_here/$dbpass/g" wp-config.php
#create uploads folder and set permissions
mkdir wp-content/uploads
chmod 777 wp-content/uploads
#remove zip file
rm latest.tar.gz
#remove bash script
rm wp.sh
fi
