# UPGRADE FROM 1.2 to 1.3

## General

### Fix BC breaks

If you have a standard installation with some custom code inside, the following command allows to update changed services or use statements.

*It does not cover all possible BC breaks, as the changes of arguments of a service, consider using this script on versioned files to be able to check the changes with a `git diff` for instance*

Based on a pim standard installation, execute the following command in your project folder :

```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\AttributeFilterInterface/CatalogBundle\\Doctrine\\Query\\AttributeFilterInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\FieldFilterInterface/CatalogBundle\\Doctrine\\Query\\FieldFilterInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\AttributeSorterInterface/CatalogBundle\\Doctrine\\Query\\AttributeSorterInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\FieldSorterInterface/CatalogBundle\\Doctrine\\Query\\FieldSorterInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\ProductQueryBuilderInterface/CatalogBundle\\Doctrine\\Query\\ProductQueryBuilderInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\ProductQueryBuilder/CatalogBundle\\Doctrine\\Query\\ProductQueryBuilder/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\ORM\\CriteriaCondition/CatalogBundle\\Doctrine\\ORM\\Condition\\CriteriaCondition/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\ORM\\ValueJoin/CatalogBundle\\Doctrine\\ORM\\Join\\ValueJoin/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\ORM\\CompletenessJoin/CatalogBundle\\Doctrine\\ORM\\Join\\CompletenessJoin/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Entity\\Family/CatalogBundle\\Model\\FamilyInterface/g'
```

## CatalogBundle

The ProductQueryBuilder has been re-worked to provide a more solid, extensible and fluent API (cf technical doc).

It's now instanciated from the ProductQueryFactory and it's not anymore a service.

## DataGridBundle

The ProductDatasource has been re-worked to create its own instance of product query builder (PQB).

Product filters and Sorters have been updated to rely on the PQB and avoid to directly manipulate Doctrine QB.

The ProductPersister has been replaced by ProductSaver.
