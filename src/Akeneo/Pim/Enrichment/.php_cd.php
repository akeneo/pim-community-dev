<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();

$builder = new RuleBuilder();

$rules = [
    $builder->only([
        'Doctrine',
        'Symfony\Component',
        'Symfony\Bundle',
        'Akeneo\Tool',
        'Akeneo\Pim\Enrichment\Component',
        'Oro\Bundle\SecurityBundle\SecurityFacade',
        'Oro\Bundle\SecurityBundle\Annotation\AclAncestor',
        // TODO the feature use the datagrid
        'Oro\Bundle\DataGridBundle',
        // TODO dependencies related to the front end
        'Twig_SimpleFunction',
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\ObjectIdentifierType',
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\TranslatableFieldType',
        'Akeneo\Platform\Bundle\UIBundle\Form\Subscriber\DisableFieldSubscriber',
        'Akeneo\Platform\Bundle\UIBundle\Form\Subscriber\DisableFieldSubscriber',
        'Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface',
        'Akeneo\Platform\Bundle\UIBundle\Provider\TranslatedLabelsProviderInterface',
        'Liip\ImagineBundle',
        // TODO it should be registered in the structure bundle
        'Akeneo\Pim\Structure\Bundle\DependencyInjection\Compiler\RegisterAttributeTypePass',
        // TODO remove all links by reference
        'Akeneo\Pim\Structure\Component\Model\AttributeInterface',
        'Akeneo\Channel\Component\Model\ChannelInterface',
        'Akeneo\Pim\Structure\Component\Model\FamilyInterface',
        'Akeneo\Channel\Component\Model\LocaleInterface',
        'Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface',
        'Akeneo\Pim\Structure\Component\Model\FamilyInterface',
        'Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface',
        'Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface',
        'Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface',
        'Akeneo\UserManagement\Component\Model\UserInterface',
        'Akeneo\Pim\Structure\Component\Model\GroupTypeInterface',
        // TODO relationship between bounded context (query data though repository)
        'Akeneo\Channel\Component\Repository\LocaleRepositoryInterface',
        'Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface',
        'Akeneo\Channel\Component\Repository\ChannelRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\FamilyVariantRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\AttributeRequirementRepositoryInterface',
        'Pim\Component\Enrich\Query\SelectedForMassEditInterface',
        'Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface',
        // TODO public constant
        'Akeneo\Pim\Structure\Component\AttributeTypes',
        // TODO the feature use the datagrid
        'Oro\Bundle\PimDataGridBundle',
        'Oro\Bundle\PimDataGridBundle',
        // TODO rely on ElasticSearch client
        'Elasticsearch\Common\Exceptions\ServerErrorResponseException',
        // TODO rely on Gedmo library
        'Gedmo\Tree\Entity\Repository\NestedTreeRepository',
        'Gedmo\Exception\UnexpectedValueException',
        // TODO misc
        'Akeneo\UserManagement\Bundle\Context\UserContext',
        'Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface',
        'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents',
        'Akeneo\Platform\Bundle\DashboardBundle\Widget\WidgetInterface',
        'Akeneo\Platform\Bundle\UIBundle\Flash\Message',
        'Akeneo\Platform\Bundle\UIBundle\Resolver\LocaleResolver',
        'Akeneo\Platform\Bundle\UIBundle\Provider\StructureVersion\StructureVersionProviderInterface',
    ])->in('Akeneo\Pim\Enrichment\Bundle'),
    $builder->only([
        'Symfony\Component',
        'Akeneo\Tool\Component',
        'Doctrine\Common',
        'InvalidArgumentException',
        // TODO remove all links by reference
        'Akeneo\UserManagement\Component\Model\UserInterface',
        'Akeneo\Channel\Component\Model\LocaleInterface',
        'Akeneo\Channel\Component\Model\ChannelInterface',
        'Akeneo\Pim\Structure\Component\Model\FamilyInterface',
        'Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface',
        'Akeneo\Pim\Structure\Component\Model\AttributeInterface',
        'Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface',
        'Akeneo\Pim\Structure\Component\Model\CommonAttributeCollection',
        'Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface',
        'Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface',
        'Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface',
        'Akeneo\Pim\Structure\Component\Model\GroupTypeInterface',
        // TODO relationship between bounded context (query data though repository)
        'Akeneo\Channel\Component\Repository\ChannelRepositoryInterface',
        'Akeneo\Channel\Component\Repository\LocaleRepositoryInterface',
        'Akeneo\Channel\Component\Query\FindActivatedCurrenciesInterface',
        'Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\GroupTypeRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface',
        'Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\FamilyVariantRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface',
        'Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Query\FindAttributeGroupOrdersEqualOrSuperiorTo',
        'Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface',
        'Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder',
        // TODO public constant
        'Akeneo\Pim\Structure\Component\AttributeTypes',
        // TODO a domain object should not log anything
        'Psr\Log\LoggerInterface',
        // TODO a bundle should not rely on a bundle
        'Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter',
        'Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasureException',
        'Akeneo\Tool\Bundle\MeasureBundle\Manager\MeasureManager',
        // TODO a component should not rely a concrete implementation
        'Doctrine\ORM\EntityRepository',
        // TODO permission management (and it should not rely on bundle neither)
        'Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface',
        'Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface',
        'Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface',
        // TODO it should only be used in the bundle (securityè)
        'Oro\Bundle\SecurityBundle\SecurityFacade',
        // TODO normalization for front end purpose
        'Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface',
        'Akeneo\Platform\Bundle\UIBundle\Provider\StructureVersion\StructureVersionProviderInterface',
        // TODO misc
        'Akeneo\UserManagement\Bundle\Context\UserContext',
        'Akeneo\UserManagement\Bundle\Manager\UserManager',
        'Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\MassActionRepositoryInterface',
        'Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager',
        'Akeneo\Pim\Structure\Component\Validator\Constraints\ValidMetric',
        'Akeneo\Pim\Enrichment\Bundle\Context\CatalogContext',
        'Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface',
    ])->in('Akeneo\Pim\Enrichment\Component'),
];

$config = new Configuration($rules, $finder);

return $config;
