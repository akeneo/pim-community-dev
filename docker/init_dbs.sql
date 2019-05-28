CREATE DATABASE akeneo_pim;
CREATE USER akeneo_pim@'%' IDENTIFIED BY 'akeneo_pim';
GRANT ALL ON akeneo_pim.* TO akeneo_pim@'%';

CREATE DATABASE akeneo_pim_test;
CREATE USER akeneo_pim_test@'%' IDENTIFIED BY 'akeneo_pim_test';
GRANT ALL ON akeneo_pim_test.* TO akeneo_pim_test@'%';
