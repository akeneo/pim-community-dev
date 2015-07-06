# UPGRADE FROM 1.3 to 1.4

> Please perform a backup of your database before proceeding to the migration. You can use tools like  [mysqldump](http://dev.mysql.com/doc/refman/5.1/en/mysqldump.html) and [mongodump](http://docs.mongodb.org/manual/reference/program/mongodump/).

> Please perform a backup of your codebase if you don't use any VCS.

WorkflowBundle
--------------

Workflowbundle has been reworked to propose a new feature: the import of proposals.
In previous version, drafts could only be proposed via the product edit form, and structure of bundle was build around form events.
All BC breaks have been written in CHANGELOG.md file.
To start again on a clean basis, we have changed the storage format (which was serialized before) to JSON (but normally you should not much be impacted by this internal change).
The new structure is more simple to read and respect our exchange format.

```
             Old structure                       |                  New structure               
-------------------------------------------------------------------------------------------------
["values": [                                     |["values": [
    "description-en_US-ecommerce": [             |    "description": [
         "text": "Text of description",          |        [
         "__context__": [                        |            "value": "Text of description",
             "attribute": "description",         |            "locale": "en_US",
             "locale": "en_US",                  |            "scope": "ecommerce"
             "scope": "ecommerce"                |        ],
         ]                                       |        [
    ],                                           |             "value": "Text of description",
    "description-en_US-mobile": [                |             "locale": "en_US",
        "text": "Text of description",           |             "scope": "mobile"
        "__context__": [                         |        ]
            "attribute": "description",          |    ]
            "locale": "en_US",                   |]
            "scope": "mobile"                    |
        ]                                        |
    ]                                            |
]                                                |
```

To migrate all drafts structure to new JSON structure, you can execute the following command in your project folder:

```
    php upgrades/1.3-1.4/common/migrate_draft.php --env=YOUR_ENV
```



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
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\CategoryRepository/Pim\\Bundle\\ClassificationBundle\\Doctrine\\ORM\\Repository\\CategoryRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Repository\\CategoryRepositoryInterface/Pim\\Component\\Classification\\Repository\\CategoryRepositoryInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pimee_security.entity.category_access.class/pimee_security.entity.product_category_access.class/g'
```
