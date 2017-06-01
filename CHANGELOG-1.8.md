# 1.8.*

## Functional improvements

- TIP-718: Update group types form
- GITHUB-4877: Update some tooltips messages of the export builder, Cheers @Milie44!
- GITHUB-5949: Fix the deletion of a job instance (import/export) from the job edit page, cheers @BatsaxIV !

## Technical improvements

- TIP-711: Rework job execution reporting page with the new PEF architecture
- TIP-724: Refactoring of the 'Settings/Association types' index screen using 'pim/common/grid'
- TIP-725: Generalization of the refactoring made in the TIP-724 for all screen containing a simple grid
- TIP-734: Menu and index page is now using the new PEF architecture
- GITHUB-6174: Show a loading mask during the file upload in the import jobs

## BC breaks

### Classes

- Remove class `Pim\Bundle\EnrichBundle\Form\Type\AttributeRequirementType`

### Constructors

- Change the constructor of `Pim\Component\Catalog\Updater\AttributeGroupUpdater` to add `Akeneo\Component\Localization\TranslatableUpdater`
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\JobTrackerController` to add `Oro\Bundle\SecurityBundle\SecurityFacade` and add an associative array
- Change the constructor of `Pim\Component\Catalog\Updater\FamilyUpdater` to add `Akeneo\Component\Localization\TranslatableUpdater`
- Change the constructor of `Pim\Component\Catalog\Updater\AttributeUpdater` to add `Akeneo\Component\Localization\TranslatableUpdater`
- Change the constructor of `Akeneo\Bundle\BatchBundle\Launcher\SimpleJobLauncher` to add `kernel.logs_dir`
- Change the constructor of `Pim\Bundle\EnrichBundle\Twig\AttributeExtension` to remove `pim_enrich.attribute_icons`
- Romved OroNotificationBundle
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\AttributeGroupController` to add `Oro\Bundle\SecurityBundle\SecurityFacade`, `Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface`, `Symfony\Component\Validator\ValidatorInterface`, `Akeneo\Component\StorageUtils\Saver\SaverInterface`, `Akeneo\Component\StorageUtils\Remover\RemoverInterface`, `Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface`
- Change the constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\SetAttributeRequirements` to remove `Pim\Component\Catalog\Repository\AttributeRepositoryInterface` and remove `Pim\Component\Catalog\Factory\AttributeRequirementFactory`
- Change the constructor of `Pim\Bundle\ApiBundle\EventSubscriber\CheckHeadersRequestSubscriber` to add `Pim\Bundle\ApiBundle\Negotiator\ContentTypeNegotiator`
- Change the constructor of `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\MultipleOptionValueUpdatedQueryGenerator` to add `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData\AttributeOptionNormalizer`

## Requirements

- GITHUB-5937: Remove the need to have mcrypt installed

## Bug Fixes

- GITHUB-6101: Fix Summernote (WYSIWYG) style
