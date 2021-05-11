#!/bin/sh

mysql -u root -p${MYSQL_ROOT_PASSWORD} -e "CREATE DATABASE akeneo_pim_test"
mysql -u root -p${MYSQL_ROOT_PASSWORD} -e "GRANT ALL PRIVILEGES ON akeneo_pim_test.* TO '${MYSQL_USER}'@'%'"
