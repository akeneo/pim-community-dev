# UPGRADE FROM 1.4 to 1.5

> Please perform a backup of your database before proceeding to the migration. You can use tools like  [mysqldump](http://dev.mysql.com/doc/refman/5.1/en/mysqldump.html) and [mongodump](http://docs.mongodb.org/manual/reference/program/mongodump/).

> Please perform a backup of your codebase if you don't use any VCS.

Partially fix BC breaks
-----------------------

If you have a standard installation with some custom code inside, the following command allows to update changed services or use statements.

**It does not cover all possible BC breaks, as the changes of arguments of a service, consider using this script on versioned files to be able to check the changes with a `git diff` for instance.**

Based on a PIM standard installation, execute the following command in your project folder:
```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ProductRule\\ValueAction/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ProductRule\\PropertyAction/g'
```
