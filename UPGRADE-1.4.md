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
         "__context__": [                        |            "data": "Text of description",
             "attribute": "description",         |            "locale": "en_US",
             "locale": "en_US",                  |            "scope": "ecommerce"
             "scope": "ecommerce"                |        ],
         ]                                       |        [
    ],                                           |             "data": "Text of description",
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

Migration to Symfony 2.7
------------------------

PIM has been migrated to Symfony 2.7.
You can read this guide to see all modifications: https://gist.github.com/mickaelandrieu/5211d0047e7a6fbff925.
 
You can execute the following commands in your project folder:
```
    find ./src -type f -print0 | xargs -0 sed -i 's/use Symfony\\Component\\OptionsResolver\\OptionsResolverInterface;/use Symfony\\Component\\OptionsResolver\\OptionsResolver;/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/public function setDefaultOptions(OptionsResolverInterface $resolver)/public function configureOptions(OptionsResolver $resolver)/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/var OptionsResolverInterface/var OptionsResolver/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/* @return OptionsResolverInterface/* @return OptionsResolver/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/use Symfony\\Component\\Validator\\ValidatorInterface;/use Symfony\\Component\\Validator\\Validator\\ValidatorInterface;/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/use Symfony\\Component\\Form\\Extension\\Core\\View\\ChoiceView;/use Symfony\\Component\\Form\\ChoiceList\\View\\ChoiceView;/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/use Symfony\\Component\\Form\\Tests\\Extension\\Core\\Type\\TypeTestCase;/use Symfony\\Component\\Form\\Test\\TypeTestCase;/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/use Symfony\\Component\\Validator\\MetadataFactoryInterface;/use Symfony\\Component\\Validator\\Mapping\\Factory\\MetadataFactoryInterface;/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/use Symfony\\Component\\Validator\\ExecutionContextInterface;/use Symfony\\Component\\Validator\\Context\\ExecutionContextInterface;/g'
```

In 2.7, the `Symfony\Component\Security\Core\SecurityContext` is marked as deprecated in favor of the `Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface` and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface` (see: http://symfony.com/blog/new-in-symfony-2-6-security-component-improvements).
```
   isGranted  => AuthorizationCheckerInterface
   getToken   => TokenStorageInterface
   setToken   => TokenStorageInterface
```

To use TokenStorageInterface:
```
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/use Symfony\\Component\\Security\\Core\\SecurityContextInterface;/use Symfony\\Component\\Security\\Core\\Authentication\\Token\\Storage\\TokenStorageInterface;/g'
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/SecurityContextInterface/TokenStorageInterface/g'
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/$this->securityContext/$this->tokenStorage/g'
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/getSecurityContext/getTokenStorage/g'
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/security.context/security.token_storage/g'
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/SecurityContext::/Security::/g'
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/$securityContext/$tokenStorage/g'
```

To use AuthorizationCheckerInterface:
```
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/use Symfony\\Component\\Security\\Core\\SecurityContextInterface;/use Symfony\\Component\\Security\\Core\\Authorization\\AuthorizationCheckerInterface;/g'
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/SecurityContextInterface/AuthorizationCheckerInterface/g'
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/$this->securityContext/$this->authorizationChecker/g'
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/getSecurityContext/getAuthorizationChecker/g'
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/security.context/security.authorization_checker/g'
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/SecurityContext::/Security::/g'
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/$securityContext/$authorizationChecker/g'
```

Miscellaneous
-------------

`imagemagick` is now a requirement of the PIM to transform asset images. On Debian and Ubuntu it can be installed via the following command:
```
    apt-get install imagemagick
 ```

Partially fix BC breaks
-----------------------

If you have a standard installation with some custom code inside, the following command allows to update changed services or use statements.

**It does not cover all possible BC breaks, as the changes of arguments of a service, consider using this script on versioned files to be able to check the changes with a `git diff` for instance.**

Based on a PIM standard installation, execute the following command in your project folder:
```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Doctrine\\MongoDBODM\\ProductMassActionRepository/PimEnterprise\\Bundle\\CatalogBundle\\Doctrine\\MongoDBODM\\Repository\\ProductMassActionRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Entity\\Repository\\AttributeRepository/PimEnterprise\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\AttributeRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Doctrine\\ORM\\ProductMassActionRepository/PimEnterprise\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\ProductMassActionRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Datagrid\\Product\\ContextConfigurator/PimEnterprise\\Bundle\\DataGridBundle\\Datagrid\\Configuration\\Product\\ContextConfigurator/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Datagrid\\Product\\FiltersConfigurator/PimEnterprise\\Bundle\\DataGridBundle\\Datagrid\\Configuration\\Product\\FiltersConfigurator/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Datagrid\\Product\\RowActionsConfigurator/PimEnterprise\\Bundle\\DataGridBundle\\Datagrid\\Configuration\\Product\\RowActionsConfigurator/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Datagrid\\ProductDraft\\GridHelper/PimEnterprise\\Bundle\\DataGridBundle\\Datagrid\\Configuration\\Product\\Draft\\GridHelper/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Datagrid\\ProductHistory\\GridHelper/PimEnterprise\\Bundle\\DataGridBundle\\Datagrid\\Configuration\\Product\\History\\GridHelper/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Datagrid\\Proposal\\ContextConfigurator/PimEnterprise\\Bundle\\DataGridBundle\\Datagrid\\Configuration\\Proposal\\ContextConfigurator/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Datagrid\\Proposal\\GridHelper/PimEnterprise\\Bundle\\DataGridBundle\\Datagrid\\Configuration\\Proposal\\GridHelper/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Datagrid\\PublishedProduct\\GridHelper/PimEnterprise\\Bundle\\DataGridBundle\\Datagrid\\Configuration\\Publish\edProduct\\GridHelper/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Doctrine\\MongoDBODM\\ProductDraftRepository/PimEnterprise\\Bundle\\WorkflowBundle\\Doctrine\\MongoDBODM\\Repository\\ProductDraftRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Doctrine\\MongoDBODM\\PublishedProductRepository/PimEnterprise\\Bundle\\WorkflowBundle\\Doctrine\\MongoDBODM\\Repository\\PublishedProductRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Doctrine\\ORM\\ProductDraftRepository/PimEnterprise\\Bundle\\WorkflowBundle\\Doctrine\\ORM\\Repository\\ProductDraftRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Doctrine\\ORM\\PublishedAssociationRepository/PimEnterprise\\Bundle\\WorkflowBundle\\Doctrine\\ORM\\Repository\\PublishedAssociationRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Doctrine\\ORM\\PublishedProductRepository/PimEnterprise\\Bundle\\WorkflowBundle\\Doctrine\\ORM\\Repository\\PublishedProductRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\CategoryRepository/Pim\\Bundle\\ClassificationBundle\\Doctrine\\ORM\\Repository\\CategoryRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Repository\\CategoryRepositoryInterface/Pim\\Component\\Classification\\Repository\\CategoryRepositoryInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pimee_security.entity.category_access.class/pimee_security.entity.product_category_access.class/g'
```
