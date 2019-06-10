# 3.2.x

## Improvements

- DAPI-138: Always display the button _Send for approval_ on the PEF as a shortcut to save and send a draft for approval
- DAPI-262: Add a create attribute action on the Franklin Insights mapping screen.
- DAPI-271: Always display suggested value and attribute type on the Franklin Insights mapping screen.
- DAPI-270: Add a progress bar on the attribute mapping screen of Franklin Insights.





- DAPI-137: Add possibility to filter by draft status in the product grid





















## Bug fixes

- GITHUB-10083: Fix proposal datagrid render when deleting values










































## Technical improvement

## BC breaks

- Change constructor of `Akeneo\Pim\Automation\RuleEngine\Component\Connector\Processor\Denormalization\RuleDefinitionProcessor` to add `Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface` and `Akeneo\Tool\Component\FileStorage\File\FileStorerInterface`
- The ValueCollection interface has been renamed into WriteValueCollectionInterface please apply `find ./src/ -type f -print0 | xargs -0 sed -i 's/ValueCollectionInterface/WriteValueCollectionInterface/g`
- Change constructor of `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer\ProductProposalNormalizer` to add `Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory` and `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`
- Change constructor of `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer\ProductModelProposalNormalizer` to add `Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory` and `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`
- Change constructor of `Akeneo\Pim\Permission\Bundle\Persistence\ORM\EntityWithValue\ProductQueryBuilderFactory` to replace the parameters `accessLevel` and `categoryAccessRepository` by an implementation of `Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetAllGrantedCategoryCodes` 
- Remove method `getGrantedCategoryQB` from `Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository`
- Remove method `getGrantedCategoryCodes` from `Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository` in favor of `Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetAllGrantedCategoryCodes` implementations


























































