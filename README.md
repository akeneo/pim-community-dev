Akeneo PIM Enterprise Application
=================================
Welcome to Akeneo PIM Enterprise product.

This repository is used to develop the Akeneo PIM Enterprise product.
Practically, it means the Akeneo PIM bundles are present in the src/ directory.

Requirements
------------
## System
 - PHP 5.4.* above 5.4.4
 - PHP Modules:
    - php5-curl
    - php5-gd
    - php5-intl
    - php5-mysql
    - php5-mcrypt
    - php-apc for PHP 5.4 (opcode and data cache)
    - php5-apcu for PHP 5.5 (for data cache, as opcode cache usually included)
 - PHP memory_limit at least at 256 MB on Apache side and 728 MB on CLI side (needed for installation, can be lowered to 512MB after installation for PHP-CLI)
 - MySQL 5.1 or above
 - Apache mod rewrite enabled

## Web browsers
 - tested: Chrome & Firefox
 - should work: IE 10, Safari
 - will not work: IE < 10
