<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$builder = new RuleBuilder();
$finder = new DefaultFinder();

$rules = [
    $builder->only([
        //It used the Channel Component --> Channel is allowed
        //TODO Link by id instead of reference
        'Akeneo\Channel\Component\Model\ChannelInterface',
        'Akeneo\Channel\Component\Model\LocaleInterface',
        'Akeneo\Channel\Component\Repository\ChannelRepositoryInterface',
        'Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface',
        'Akeneo\Channel\Component\Repository\LocaleRepositoryInterface',

        //TODO See how to deal with assets
        'Akeneo\Asset\Bundle\AttributeType\AttributeTypes',
        'Akeneo\Asset\Component\Model\AssetInterface',
        'Akeneo\Asset\Component\Repository\AssetRepositoryInterface',

        //It uses the Enrichment Commponent --> Enrichment is allowed
        //TODO Link by id instead of reference
        'Akeneo\Pim\Enrichment\Bundle\Command',
        'Akeneo\Pim\Enrichment\Bundle\Doctrine',
        'Akeneo\Pim\Enrichment\Bundle\Elasticsearch',
        'Akeneo\Pim\Enrichment\Bundle\Filter',
        'Akeneo\Pim\Enrichment\Component\Product',
        'Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface',

        //It uses the permissions
        'Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository',
        'Akeneo\Pim\Permission\Bundle\User\UserContext',
        'Akeneo\Pim\Permission\Component',

        //It uses the StructureComponent --> Structure is allowed
        'Akeneo\Pim\Structure\Component\AttributeTypes', // Used by the presenters
        'Akeneo\Pim\Structure\Component\Factory\AttributeFactory', // Used by twig in the datagrid

        //TODO Link by id instead of reference
        'Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface',
        'Akeneo\Pim\Structure\Component\Model\AttributeInterface',
        'Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface',
        'Akeneo\Pim\Structure\Component\Model\FamilyInterface',
        'Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface', //For the reference data filter
        'Akeneo\Pim\Structure\Component\Repository',

        //It uses the Workflow component
        'Akeneo\Pim\WorkOrganization\Workflow\Component',

        //It uses the UI
        'Akeneo\Platform\Bundle\DashboardBundle',
        'Akeneo\Platform\Bundle\NotificationBundle',
        'Akeneo\Platform\Bundle\UIBundle',

        //Tool used by the workflow
        'Akeneo\Tool\Bundle\ApiBundle',
        'Akeneo\Tool\Bundle\BatchBundle',
        'Akeneo\Tool\Bundle\ElasticsearchBundle',
        'Akeneo\Tool\Bundle\MeasureBundle',
        'Akeneo\Tool\Bundle\StorageUtilsBundle',
        'Akeneo\Tool\Bundle\VersioningBundle',
        'Akeneo\Tool\Component\Analytics',
        'Akeneo\Tool\Component\Api',
        'Akeneo\Tool\Component\Batch',
        'Akeneo\Tool\Component\Connector',
        'Akeneo\Tool\Component\FileStorage',
        'Akeneo\Tool\Component\Localization',
        'Akeneo\Tool\Component\StorageUtils',
        'Akeneo\Tool\Component\Versioning',
        'Akeneo\UserManagement\Bundle\Context\UserContext',
        'Akeneo\UserManagement\Bundle\Manager\UserManager',
        'Akeneo\UserManagement\Component\Event\UserEvent',
        'Akeneo\UserManagement\Component\Model\UserInterface',
        'Akeneo\UserManagement\Component\Repository\UserRepositoryInterface',

        //NORMAL COUPLING
        'Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass',
        'Doctrine\Common\Util\ClassUtils',
        'Doctrine\Common\EventSubscriber',
        'Doctrine\Common\Persistence\ObjectManager',
        'Doctrine\Common\Persistence\ObjectRepository',
        'Doctrine\DBAL\Connection',
        'Doctrine\ORM\EntityManagerInterface',
        'Doctrine\ORM\EntityRepository',
        'Doctrine\ORM\Event\LifecycleEventArgs',
        'Doctrine\ORM\NoResultException',
        'Doctrine\ORM\QueryBuilder',
        'Elasticsearch\Common\Exceptions\ServerErrorResponseException',

        //It uses the datagrid it should not use Oro directly but Pim as an ACL
        'Oro\Bundle\DataGridBundle',
        'Oro\Bundle\FilterBundle',
        'Oro\Bundle\PimDataGridBundle',
        'Oro\Bundle\PimFilterBundle',
        'Oro\Bundle\SecurityBundle',

        //TODO Lazy load command
        'Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand',
        'Symfony\Bundle\FrameworkBundle\Templating\EngineInterface',
        'Symfony\Component\Config',
        'Symfony\Component\Console',
        'Symfony\Component\DependencyInjection',
        'Symfony\Component\EventDispatcher',
        'Symfony\Component\Form\FormFactoryInterface',
        'Symfony\Component\HttpFoundation',
        'Symfony\Component\HttpKernel',
        'Symfony\Component\OptionsResolver\OptionsResolver',
        'Symfony\Component\Routing',
        'Symfony\Component\Security',
        'Symfony\Component\Serializer',
        'Symfony\Component\Translation',
        'Symfony\Component\Validator'

    ])->in('Akeneo\Pim\WorkOrganization\Workflow\Bundle'),
//    $builder->only([
//        'Symfony\Component',
//    ])->in('Akeneo\Pim\WorkOrganization\Workflow\Component'),
];

$config = new Configuration($rules, $finder);

return $config;

