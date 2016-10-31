# UPGRADE FROM 1.6 to 1.7

## Disclaimer

> Please check that you're using Akeneo PIM v1.6

> We're assuming that you created your project from the standard distribution

> This documentation helps to migrate projects based on the Community Edition and the Enterprise Edition

> Please perform a backup of your database before proceeding to the migration. You can use tools like [mysqldump](http://dev.mysql.com/doc/refman/5.1/en/mysqldump.html) and [mongodump](http://docs.mongodb.org/manual/reference/program/mongodump/).

> Please perform a backup of your codebase if you don't use a VCS (Version Control System).


## Migrate your system requirements

## Migrate your standard project

## Migrate your custom code

### Global updates for any project

#### Update references to moved `Pim\Bundle\ConnectorBundle\Reader` business classes

In order to be more precise about the roles our existing file iterators have we renamed some existing classed as existing file iterators would only supports only tabular file format like CSV and XLSX.

Please execute the following commands in your project folder to update the references you may have to these classes:
```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\File\\FileIterator/Pim\\Component\\Connector\\Reader\\File\\FlatFileIterator/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_connector\.reader\.file\.file_iterator\.class/pim_connector\.reader\.file\.flat_file_iterator\.class/g'
```
