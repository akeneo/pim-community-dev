# UPGRADE FROM 1.5 to 1.6

```
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\SecurityBundle\\Model/PimEnterprise\\Component\\Security\\Model/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\SecurityBundle\\Entity\\Repository\\AccessRepositoryInterface/PimEnterprise\\Component\\Security\\Repository\\AccessRepositoryInterface/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Model/PimEnterprise\\Component\\Workflow\\Model/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Builder\\ProductDraftBuilderInterface/PimEnterprise\\Component\\Workflow\\Builder\\ProductDraftBuilderInterface/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Event\\ProductDraftEvents/PimEnterprise\\Component\\Workflow\\Event\\ProductDraftEvents/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Event\\PublishedProductEvent/PimEnterprise\\Component\\Workflow\\Event\\PublishedProductEvent/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Event\\PublishedProductEvents/PimEnterprise\\Component\\Workflow\\Event\\PublishedProductEvents/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Factory/PimEnterprise\\Component\\Workflow\\Factory/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Normalizer/PimEnterprise\\Component\\Workflow\\Normalizer/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Applier/PimEnterprise\\Component\\Workflow\\Applier/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Repository/PimEnterprise\\Component\\Workflow\\Repository/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Helper\\SortProductValuesHelper/PimEnterprise\\Bundle\\WorkflowBundle\\Twig\\SortProductValuesHelper/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Publisher/PimEnterprise\\Component\\Workflow\\Publisher/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Connector\\Tasklet/PimEnterprise\\Component\\Workflow\\Connector\\Tasklet/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Model/PimEnterprise\\Component\\Catalog\\Model/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\SecurityBundle\\Attributes/PimEnterprise\\Component\\Security\\Attributes/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Publisher\\Product\\FilePublisher/PimEnterprise\\Component\\Workflow\\Publisher\\Product\\FileInfoPublisher/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/ Pim\\Bundle\\TransformBundle\\Normalizer\\Flat/PimEnterprise\\Bundle\\VersioningBundle\\Normalizer\\Flat/g'
```
