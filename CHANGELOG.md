# master

## Bug fixes

- PIM-9595: Avoid 403 error when launching import with no view rights on import details
- PIM-9622: Fix query that can generate a MySQL memory allocation error

## New features

## Improvements

# Technical Improvements

## Classes

## BC Breaks

- CPM-101: Remove twig/extensions dependency (abandoned)
- CPM-100: replace deprecated `Symfony\Component\Translation\TranslatorInterface` by `Symfony\Contracts\Translation\TranslatorInterface`
- CPM-100: replace deprecated `Symfony\Component\HttpKernel\Event\GetResponseEvent` by `Symfony\Component\HttpKernel\Event\RequestEvent`
- CPM-99: replace removed `Doctrine\Common\Persistence\ObjectRepository` class by `Doctrine\Persistence\ObjectRepository`
- CPM-99: replace removed `Doctrine\Common\Persistence\ObjectManager` class by `Doctrine\Persistence\ObjectManager`
- CPM-99: replace removed `Doctrine\Common\Persistence\ManagerRegistry` class by `Doctrine\Persistence\ManagerRegistry`
- CPM-99: replace removed `Doctrine\Common\Persistence\Event\LifecycleEventArgs` class by `Doctrine\ORM\Event\LifecycleEventArgs`

### Codebase

- Change constructor of `Oro\Bundle\PimDataGridBundle\Controller\DatagridController` to remove `Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating`
- Change constructor of `Oro\Bundle\FilterBundle\Form\Type\Filter\DateTimeRangeFilterType` to remove `Symfony\Component\Translation\TranslatorInterface $translator`
- Change constructor of `Oro\Bundle\PimFilterBundle\Filter\ProductValue\MetricFilter` to remove `Symfony\Component\Translation\TranslatorInterface $translator`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\VersionNormalizer` to add `Symfony\Contracts\Translation\LocaleAwareInterface\LocaleAwareInterface $localeAware`
- Change constructor of `Akeneo\UserManagement\Bundle\EventListener\LocaleSubscriber` to:
    - remove `Symfony\Component\Translation\TranslatorInterface $translator`
    - add  `Symfony\Contracts\Translation\LocaleAwareInterface\LocaleAwareInterface $localeAware`
- Change `Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\MappingsOverrideConfiguratorInterface::configure()` to replace `Doctrine\Common\Persistence\Mapping\ClassMetadata` first argument by `Doctrine\ORM\Mapping\ClassMetadata`
- Change `Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\ORM\MappingsOverrideConfigurator::configure()` to replace `Doctrine\Common\Persistence\Mapping\ClassMetadata` first argument by `Doctrine\ORM\Mapping\ClassMetadata`
- Change `Akeneo\Tool\Bundle\StorageUtilsBundle\EventSubscriber\ConfigureMappingsSubscriber::loadClassMetadata()` to replace `Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs` first argument by `Doctrine\ORM\Event\LoadClassMetadataEventArgs`
- Change `Akeneo\Tool\Bundle\StorageUtilsBundle\EventSubscriber\ResolveTargetRepositorySubscriber::loadClassMetadata()` to replace `Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs` first argument by `Doctrine\ORM\Event\LoadClassMetadataEventArgs`

### CLI commands

### Services

