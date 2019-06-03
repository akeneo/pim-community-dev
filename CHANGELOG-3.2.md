# 3.2.x

## Improvements

## Bug fixes

- GITHUB-10083: Fix proposal datagrid render when deleting values

## Technical improvement

## BC breaks

- Change constructor of `Akeneo\Pim\Automation\RuleEngine\Component\Connector\Processor\Denormalization\RuleDefinitionProcessor` to add `Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface` and `Akeneo\Tool\Component\FileStorage\File\FileStorerInterface`
- The ValueCollection interface has been renamed into WriteValueCollectionInterface please apply `find ./src/ -type f -print0 | xargs -0 sed -i 's/ValueCollectionInterface/WriteValueCollectionInterface/g`
