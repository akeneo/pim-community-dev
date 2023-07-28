<?php

declare(strict_types=1);

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$finder->notPath('tests');

$builder = new RuleBuilder();

$rules = [
    $builder->only([
        'Akeneo\Category\Domain',

        // TBD
        'Akeneo\Category\Api\Command\UpsertCategoryCommand',
        'Akeneo\Category\Api\Command\UserIntents\SetImage',
        'Akeneo\Category\Api\Command\UserIntents\SetLabel',
        'Akeneo\Category\Api\Command\UserIntents\SetRichText',
        'Akeneo\Category\Api\Command\UserIntents\SetText',
        'Akeneo\Category\Api\Command\UserIntents\SetTextArea',
        'Akeneo\Category\Api\Command\UserIntents\UserIntent',
        'Akeneo\Category\ServiceApi\Category',
        'Akeneo\Category\ServiceApi\CategoryQueryInterface',
        'Akeneo\Category\ServiceApi\ExternalApiCategory',
        'Akeneo\Category\ServiceApi\ExternalApiCategory',

        // Outside /!\
        'Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface',
        'Akeneo\Category\Infrastructure\Converter\InternalApi\InternalApiToStd',
        'Akeneo\Category\Infrastructure\Exception\ArrayConversionException',
        'Akeneo\Category\Infrastructure\Exception\ContentArrayConversionException',
        'Akeneo\Category\Infrastructure\Exception\StructureArrayConversionException',
        'Akeneo\Category\Infrastructure\Registry\FindCategoryAdditionalPropertiesRegistry',
        'Akeneo\Tool\Component\FileStorage\File\FileStorer',
        'Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface',
        'Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface',
        'Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface',
        'Oro\Bundle\SecurityBundle\SecurityFacade',
        'Akeneo\Category\Infrastructure\Validation\TemplateCodeShouldBeUnique',

        // Vendors
        'Symfony\Component\HttpFoundation\File\UploadedFile',
        'Symfony\Component\Validator',
        'Symfony\Component\HttpKernel',
        'Symfony\Component\EventDispatcher',
        'Webmozart\Assert',
        'Ramsey\Uuid\Uuid',
    ])->in('Akeneo\Category\Application'),

    $builder->only([
        // Outside /!\
        'Akeneo\Category\Api\Command\UserIntents\UserIntent',
        'Akeneo\Category\Api\Command\UserIntents\SetLabel',
        'Akeneo\Category\Api\Command\UserIntents\SetImage',
        'Akeneo\Category\Api\Command\UserIntents\SetRichText',
        'Akeneo\Category\Api\Command\UserIntents\SetText',
        'Akeneo\Category\Api\Command\UserIntents\SetTextArea',
        'Akeneo\Category\Application\Query\GetAttribute',
        'Akeneo\Category\Infrastructure\Converter\InternalApi\InternalApiToStd',
        'Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException',

        // Vendors
        'Webmozart\Assert',
        'Ramsey\Uuid',
        'Symfony\Component\Validator\ConstraintViolation',
        'Symfony\Component\Validator\ConstraintViolationList',
    ])->in('Akeneo\Category\Domain'),

    $builder->only([
        'Akeneo\Category\Application',
        'Akeneo\Category\Domain',
        'Akeneo\Category\ServiceApi',
        'Akeneo\Category\Api',

        // Other Domains Service API
        'Akeneo\Channel\Infrastructure\Component\Query\PublicApi',
        'Akeneo\UserManagement\Component\Query\PublicApi',
        'Akeneo\UserManagement\ServiceApi',
        'Akeneo\Channel\API\Query\FindLocales',
        'Akeneo\Channel\API\Query\Locale',
        // Other Domains Components
        'Akeneo\Channel\Infrastructure\Component\Model\Channel',
        'Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface',
        'Akeneo\Channel\Infrastructure\Component\Model\Locale',
        'Akeneo\Pim\Enrichment\Component\Category\Query\GetDirectChildrenCategoryCodesInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer',
        'Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface',
        'Akeneo\Pim\Enrichment\Component\Validator\Constraints\Type',
        'Akeneo\UserManagement\Component\Storage\Saver\RoleWithPermissionsSaver',
        // Other Domains Bundles /!\
        'Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\TranslationNormalizer',
        'Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository',
        'Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager',

        // Infrastructure Service API
        'Akeneo\Tool\Bundle\VersioningBundle\ServiceApi',
        // Infrastructure Components
        'Akeneo\Tool\Component',
        'Symfony\Component\OptionsResolver\OptionsResolver',
        // Infrastructure Bundles /!\
        'Akeneo\Platform\Bundle\InstallerBundle\Command\ZddMigration',
        'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent',
        'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents',
        'Akeneo\Tool\Bundle\ApiBundle\Documentation',
        'Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse',
        'Oro\Bundle\SecurityBundle\SecurityFacade',
        'Akeneo\Tool\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetModelPass',
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\TranslatableFieldType',
        'Akeneo\Platform\Bundle\UIBundle\Form\Subscriber\DisableFieldSubscriber',
        'Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository',
        'Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface',

        // Vendors
        'Symfony\Component\Config',
        'Symfony\Component\Console',
        'Symfony\Component\DependencyInjection',
        'Symfony\Component\EventDispatcher',
        'Symfony\Component\Form',
        'Symfony\Component\HttpFoundation',
        'Symfony\Component\HttpKernel',
        'Symfony\Component\Lock',
        'Symfony\Component\Mime',
        'Symfony\Component\PropertyAccess',
        'Symfony\Component\Routing',
        'Symfony\Component\Security',
        'Symfony\Component\Serializer',
        'Symfony\Component\Validator',
        'Doctrine\Common',
        'Doctrine\DBAL',
        'Doctrine\ORM',
        'Doctrine\Persistence',
        'Gedmo\Tree',
        'Liip\ImagineBundle',
        'Imagine\Exception',
        'League\Flysystem',
        'Psr\Log',
        'Twig',
        'Webmozart\Assert',
        // Vendors Bundle /!\
        'Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass',
    ])->in('Akeneo\Category\Infrastructure'),

    $builder->only([
        // Vendors
        'Symfony\Component\Messenger',
        'Webmozart\Assert',
    ])->in('Akeneo\Category\Api'),

    $builder->only([
        'Akeneo\Category\Domain',

        // Vendors
        'Webmozart\Assert',
    ])->in('Akeneo\Category\ServiceApi'),
];

$config = new Configuration($rules, $finder);

return $config;
