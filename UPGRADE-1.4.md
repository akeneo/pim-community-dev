# UPGRADE FROM 1.3 to 1.4

> Please perform a backup of your database before proceeding to the migration. You can use tools like  [mysqldump](http://dev.mysql.com/doc/refman/5.1/en/mysqldump.html) and [mongodump](http://docs.mongodb.org/manual/reference/program/mongodump/).

> Please perform a backup of your codebase if you don't use any VCS.

## Partially fix BC breaks

If you have a standard installation with some custom code inside, the following command allows to update changed services or use statements.

**It does not cover all possible BC breaks, as the changes of arguments of a service, consider using this script on versioned files to be able to check the changes with a `git diff` for instance.**

Based on a PIM standard installation, execute the following command in your project folder:

```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Doctrine\\MongoDBODM\\ProductMassActionRepository/PimEnterprise\\Bundle\\CatalogBundle\\Doctrine\\MongoDBODM\\Repository\\ProductMassActionRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Entity\\Repository\\AttributeRepository/PimEnterprise\\Bundle\\CatalogBundle\\{Entity → Doctrine\\ORM}\\Repository\\AttributeRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Doctrine\\ORM\\{ → Repository}\\ProductMassActionRepository/PimEnterprise\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\ProductMassActionRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Doctrine\\MongoDBODM\\ProductDraftRepository/PimEnterprise\\Bundle\\WorkflowBundle\\Doctrine\\MongoDBODM\\Repository\\ProductDraftRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Doctrine\\MongoDBODM\\PublishedProductRepository/PimEnterprise\\Bundle\\WorkflowBundle\\Doctrine\\MongoDBODM\\Repository\\PublishedProductRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Doctrine\\ORM\\ProductDraftRepository/PimEnterprise\\Bundle\\WorkflowBundle\\Doctrine\\ORM\\Repository\\ProductDraftRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Doctrine\\ORM\\PublishedAssociationRepository/PimEnterprise\\Bundle\\WorkflowBundle\\Doctrine\\ORM\\Repository\\PublishedAssociationRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Doctrine\\ORM\\PublishedProductRepository/PimEnterprise\\Bundle\\WorkflowBundle\\Doctrine\\ORM\\Repository\\PublishedProductRepository/g'
```