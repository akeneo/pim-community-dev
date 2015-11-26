# UPGRADE FROM 1.4 to 1.5

> Please perform a backup of your database before proceeding to the migration. You can use tools like  [mysqldump](http://dev.mysql.com/doc/refman/5.1/en/mysqldump.html) and [mongodump](http://docs.mongodb.org/manual/reference/program/mongodump/).

> Please perform a backup of your codebase if you don't use any VCS.

## Partially fix BC breaks

If you have a standard installation with some custom code inside, the following command allows to update changed services or use statements.

**It does not cover all possible BC breaks, as the changes of arguments of a service, consider using this script on versioned files to be able to check the changes with a `git diff` for instance.**

Based on a PIM standard installation, execute the following command in your project folder:
```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ProductRule\\ValueAction/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ProductRule\\PropertyAction/g'
```

## Product rule import rework

### Functionnal changes

In 1.5 we introduce a new `add` action type. This new action allows the user to add collections of items into another collection. For example with this new action you can add multiple categories to a product or add multiple options to a multi select attribute. The import syntax is as follow:

    rule_sku_jacket:
        priority: 10
        conditions:
            - field:    sku
              operator: =
              value:    my-jacket
        actions:
            - type:  add
              field: weather_conditions
              data:
                - dry
                - hot
          - type:  add
            field: categories
            data:
              - tshirts

As you can see, this is the first rule action to be able to manipulate both product values (multi select attributes) and product fields (categories). To avoid aving multiple type of rules actions with different capabilities we decided to introduce two more rule actions: `set` and `copy`. These actions are exact copies of the former `set_value` and `copy_value` actions and can manipulate both fields (enabled, families, etc) and values (sku, name, description, etc).

For backward compatibility issues we kept `set_value` and `copy_value` and you can still use them but they are deprecated and we planned to remove them in the future.

The rule import format for `set` and `copy` is exactly the same as for `set_value` and `copy_value` so the easiest way to migrate your old rules to the new format is to export them within Akeneo PIM with our standard connector, change the action type and reimport them.

### Internal changes

We decided to clean this bundle and respect our new architecture organisation. To migrate your custom action or rule engine customization, you can run the following commands at the root folder of your project (make sure that you use a versioning system before doing so).

    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Connector\\Processor\\Denormalization\\RuleDefinitionProcessor/PimEnterprise\\Component\\CatalogRule\\Connector\\Processor\\Denormalization\\RuleDefinitionProcessor/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Connector\\Processor\\Normalization\\RuleDefinitionProcessor/PimEnterprise\\Component\\CatalogRule\\Connector\\Processor\\Normalization\\RuleDefinitionProcessor/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Connector\\Writer\\Doctrine\\RuleDefinitionWriter/PimEnterprise\\Component\\CatalogRule\\Connector\\Writer\\Doctrine\\RuleDefinitionWriter/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Connector\\Writer\\YamlFile\\RuleDefinitionWriter/PimEnterprise\\Component\\CatalogRule\\Connector\\Writer\\YamlFile\\RuleDefinitionWriter/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Denormalizer\\ProductRule\\ConditionDenormalizer/PimEnterprise\\Component\\CatalogRule\\Denormalizer\\ProductRule\\ConditionDenormalizer/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Denormalizer\\ProductRule\\ContentDenormalizer/PimEnterprise\\Component\\CatalogRule\\Denormalizer\\ProductRule\\ContentDenormalizer/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Engine\\ProductRuleApplier/PimEnterprise\\Component\\CatalogRule\\Engine\\ProductRuleApplier/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Engine\\ProductRuleApplier\\ProductsSaver/PimEnterprise\\Component\\CatalogRule\\Engine\\ProductRuleApplier\\ProductsSaver/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Engine\\ProductRuleApplier\\ProductsUpdater/PimEnterprise\\Component\\CatalogRule\\Engine\\ProductRuleApplier\\ProductsUpdater/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Engine\\ProductRuleApplier\\ProductsValidator/PimEnterprise\\Component\\CatalogRule\\Engine\\ProductRuleApplier\\ProductsValidator/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Engine\\ProductRuleBuilder/PimEnterprise\\Component\\CatalogRule\\Engine\\ProductRuleBuilder/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Engine\\ProductRuleSelector/PimEnterprise\\Component\\CatalogRule\\Engine\\ProductRuleSelector/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Model\\FieldImpactActionInterface/PimEnterprise\\Component\\CatalogRule\\Model\\FieldImpactActionInterface/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Model\\ProductCondition/PimEnterprise\\Component\\CatalogRule\\Model\\ProductCondition/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Model\\ProductConditionInterface/PimEnterprise\\Component\\CatalogRule\\Model\\ProductConditionInterface/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Model\\RuleRelationInterface/PimEnterprise\\Component\\CatalogRule\\Model\\RuleRelationInterface/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Model\\RuleRelation/PimEnterprise\\Component\\CatalogRule\\Model\\RuleRelation/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Repository\\RuleRelationRepositoryInterface/PimEnterprise\\Component\\CatalogRule\\Repository\\RuleRelationRepositoryInterface/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Runner\\ProductRuleRunner/PimEnterprise\\Component\\CatalogRule\\Runner\\ProductRuleRunner/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ExistingAddField/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraint\\ExistingAddField/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ExistingCopyFields/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraint\\ExistingCopyFields/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ExistingField/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraint\\ExistingField/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ExistingFieldValidator/PimEnterprise\\Component\\CatalogRule\\Validator\\ExistingFieldValidator/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ExistingFilterField/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraint\\ExistingFilterField/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ExistingSetField/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraint\\ExistingSetField/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ProductRule\\NonEmptyValueCondition/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraint\\NonEmptyValueCondition/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ProductRule\\NonEmptyValueConditionValidator/PimEnterprise\\Component\\CatalogRule\\Validator\\NonEmptyValueConditionValidator/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ProductRule\\PropertyAction/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraint\\PropertyAction/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ProductRule\\PropertyActionValidator/PimEnterprise\\Component\\CatalogRule\\Validator\\PropertyActionValidator/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ProductRule\\SupportedOperatorCondition/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraint\\SupportedOperatorCondition/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ProductRule\\SupportedOperatorConditionValidator/PimEnterprise\\Component\\CatalogRule\\Validator\\SupportedOperatorConditionValidator/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ProductRule\\ValueCondition/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraint\\ValueCondition/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ProductRule\\ValueConditionValidator/PimEnterprise\\Component\\CatalogRule\\Validator\\ValueConditionValidator/g'
