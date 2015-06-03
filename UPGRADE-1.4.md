# UPGRADE FROM 1.3 to 1.4

> Please perform a backup of your database before proceeding to the migration. You can use tools like  [mysqldump](http://dev.mysql.com/doc/refman/5.1/en/mysqldump.html) and [mongodump](http://docs.mongodb.org/manual/reference/program/mongodump/).

> Please perform a backup of your codebase if you don't use any VCS.

## Partially fix BC breaks

If you have a standard installation with some custom code inside, the following command allows to update changed services or use statements.

**It does not cover all possible BC breaks, as the changes of arguments of a service, consider using this script on versioned files to be able to check the changes with a `git diff` for instance.**

Based on a PIM standard installation, execute the following command in your project folder:

```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\MongoDBODM\\CompletenessRepository/CatalogBundle\\Doctrine\\MongoDBODM\\Repository\\CompletenessRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\MongoDBODM\\ProductCategoryRepository/CatalogBundle\\Doctrine\\MongoDBODM\\Repository\\ProductCategoryRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\MongoDBODM\\ProductMassActionRepository/CatalogBundle\\Doctrine\\MongoDBODM\\Repository\\ProductMassActionRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\MongoDBODM\\ProductRepository/CatalogBundle\\Doctrine\\MongoDBODM\\Repository\\ProductRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\ORM\\CompletenessRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\CompletenessRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\ORM\\ProductCategoryRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\ProductCategoryRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\ORM\\ProductMassActionRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\ProductMassActionRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\ORM\\ProductRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\ProductRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Entity\\Repository\\AssociationRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\AssociationRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Entity\\Repository\\AssociationTypeRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\AssociationTypeRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Entity\\Repository\\AttributeGroupRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\AttributeGroupRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Entity\\Repository\\AttributeOptionRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\AttributeOptionRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Entity\\Repository\\AttributeRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\AttributeRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Entity\\Repository\\CategoryRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\CategoryRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Entity\\Repository\\ChannelRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\ChannelRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Entity\\Repository\\CurrencyRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\CurrencyRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Entity\\Repository\\FamilyRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\FamilyRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Entity\\Repository\\GroupRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\GroupRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Entity\\Repository\\GroupTypeRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\GroupTypeRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Entity\\Repository\\LocaleRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\LocaleRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Updater\\Setter\\AbstractValueSetter/CatalogBundle\\Updater\\Setter\\AbstractAttributeSetter/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Updater\\Setter\\BooleanValueSetter/CatalogBundle\\Updater\\Setter\\BooleanAttributeSetter/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Updater\\Setter\\DateValueSetter/CatalogBundle\\Updater\\Setter\\DateAttributeSetter/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Updater\\Setter\\MediaValueSetter/CatalogBundle\\Updater\\Setter\\MediaAttributeSetter/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Updater\\Setter\\MetricValueSetter/CatalogBundle\\Updater\\Setter\\MetricAttributeSetter/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Updater\\Setter\\MultiSelectValueSetter/CatalogBundle\\Updater\\Setter\\MultiSelectAttributeSetter/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Updater\\Setter\\NumberValueSetter/CatalogBundle\\Updater\\Setter\\NumberAttributeSetter/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Updater\\Setter\\PriceCollectionValueSetter/CatalogBundle\\Updater\\Setter\\PriceCollectionAttributeSetter/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Updater\\Setter\\SimpleSelectValueSetter/CatalogBundle\\Updater\\Setter\\SimpleSelectAttributeSetter/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Updater\\Setter\\TextValueSetter/CatalogBundle\\Updater\\Setter\\TextAttributeSetter/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Updater\\Copier\\CopierInterface/CatalogBundle\\Updater\\Copier\\AttributeCopierInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Updater\\Copier\\AbstractValueCopier/CatalogBundle\\Updater\\Copier\\AbstractAttributeCopier/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Updater\\Copier\\BaseValueCopier/CatalogBundle\\Updater\\Copier\\BaseAttributeCopier/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Updater\\Copier\\MediaValueCopier/CatalogBundle\\Updater\\Copier\\MediaAttributeCopier/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Updater\\Copier\\MetricValueCopier/CatalogBundle\\Updater\\Copier\\MetricAttributeCopier/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Updater\\Copier\\MultiSelectValueCopier/CatalogBundle\\Updater\\Copier\\MultiSelectAttributeCopier/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Updater\\Copier\\PriceCollectionValueCopier/CatalogBundle\\Updater\\Copier\\PriceCollectionAttributeCopier/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Updater\\Copier\\SimpleSelectValueCopier/CatalogBundle\\Updater\\Copier\\SimpleSelectAttributeCopier/g'
```