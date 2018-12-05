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
        'Liip\ImagineBundle',
        // TODO the feature use the datagrid
        'Oro\Bundle\DataGridBundle',
        'Oro\Bundle\PimDataGridBundle',
        // TODO: dependencies related to the front end, remove twig screens
        'Twig_SimpleFunction', // used by the category tree
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\ObjectIdentifierType',
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\TranslatableFieldType',
        'Akeneo\Platform\Bundle\UIBundle\Form\Subscriber\DisableFieldSubscriber',
        'Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface',
        'Akeneo\Platform\Bundle\UIBundle\Provider\TranslatedLabelsProviderInterface',
        // TODO: EASY PICK! it should be registered in the structure bundle
        'Akeneo\Pim\Structure\Bundle\DependencyInjection\Compiler\RegisterAttributeTypePass',
        // TODO remove all links by reference
        'Akeneo\Pim\Structure\Component\Model\AttributeInterface',
        'Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface',
        'Akeneo\Pim\Structure\Component\Model\FamilyInterface',
        'Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface',
        'Akeneo\Pim\Structure\Component\Model\GroupTypeInterface',
        'Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface',
        'Akeneo\Channel\Component\Model\ChannelInterface',
        'Akeneo\Channel\Component\Model\LocaleInterface',
        'Akeneo\UserManagement\Component\Model\UserInterface', // ideally, we should have a "Product Contributor"
        // TODO relationship between bounded context (query data though repository)
        'Akeneo\Channel\Component\Repository\LocaleRepositoryInterface',
        'Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface',
        'Akeneo\Channel\Component\Repository\ChannelRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\FamilyVariantRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\AttributeRequirementRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface',
        // TODO public constant
        'Akeneo\Pim\Structure\Component\AttributeTypes',
        // TODO: : EASY PICK! API PaginatorInterface should catch ServerErrorResponseException and throw its own exception,
        'Elasticsearch\Common\Exceptions\ServerErrorResponseException',
        // TODO: used in CategoryRepository for method findTranslatedLabels => can be reworked for a query (and remove the extend of Gedmo repository)
        'Gedmo\Tree\Entity\Repository\NestedTreeRepository',
        // TODO: we should not inject the UserContext as a service
        'Akeneo\UserManagement\Bundle\Context\UserContext',
        // TODO: use to find the repository of the reference data (often to check if the reference data exists, sometimes we really need more information)
        'Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface',
        // TODO: EASY PICK! move StructureVersion\EventListener\TableCreator to Platform
        'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents',
        'Akeneo\Platform\Bundle\UIBundle\Provider\StructureVersion\StructureVersionProviderInterface',
        // TODO: widget discussion
        'Akeneo\Platform\Bundle\DashboardBundle\Widget\WidgetInterface',
        // TODO: remove Flash\Message in CategoryTreeController
        'Akeneo\Platform\Bundle\UIBundle\Flash\Message',
        // TODO: used in ProductCommentController to retrieve current locale => frontend or not discussion for "contextual" parameters
        'Akeneo\Platform\Bundle\UIBundle\Resolver\LocaleResolver',
    ])->in('Akeneo\Pim\Enrichment\Bundle'),
    $builder->only([
        'Symfony\Component',
        'Akeneo\Tool\Component',
        'Doctrine\Common',
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
        'Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface',
        'Akeneo\Channel\Component\Query\FindActivatedCurrenciesInterface',
        'Akeneo\Pim\Structure\Component\Repository\GroupTypeRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\FamilyVariantRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface',
        'Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Query\FindAttributeGroupOrdersEqualOrSuperiorTo', //should not use bundle in component
        //TODO: should not use bundle in component
        'Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder',
        // TODO public constant
        'Akeneo\Pim\Structure\Component\AttributeTypes',
        // TODO a domain object should not log anything (today we ValueCollectionFactory for 2 things: 1. load products from DB, 2. create new products/values. For 1, we catch errors and log them. We should have 2 different factories instead)
        'Psr\Log\LoggerInterface',
        // TODO a component should not rely on a bundle
        'Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter',
        'Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasureException',
        'Akeneo\Tool\Bundle\MeasureBundle\Manager\MeasureManager',
        // TODO: EASY PICK! a component should not rely a concrete implementation => use the right repo interface
        'Doctrine\ORM\EntityRepository',
        // TODO EASY PICK! permission management (and it should not rely on bundle neither) => move to component
        'Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface',
        'Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface',
        // TODO it should only be used in the bundle (security, used to check if granted to ACL)
        'Oro\Bundle\SecurityBundle\SecurityFacade',
        // TODO normalization for front end purpose
        'Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface',
        // TODO: StructureVersionProvider discussion, for the cache of the frontend
        'Akeneo\Platform\Bundle\UIBundle\Provider\StructureVersion\StructureVersionProviderInterface',
        // TODO: do not inject user context as a service
        'Akeneo\UserManagement\Bundle\Context\UserContext',
        // TODO: used to retrieve one user (could we user UserRepositoryInterface instead)?
        'Akeneo\UserManagement\Bundle\Manager\UserManager',
        // TODO: is ProductMassActionRepositoryInterface still used?
        'Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\MassActionRepositoryInterface',
        // TODO: a component should not use a bundle
        'Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager',
        // TODO: extract from ValidMetric the enrichment part
        'Akeneo\Pim\Structure\Component\Validator\Constraints\ValidMetric',
        // TODO: should not have a contextual service
        'Akeneo\Pim\Enrichment\Bundle\Context\CatalogContext',
        // TODO: EASY PICK! move JobLauncherInterface to component
        'Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface',
    ])->in('Akeneo\Pim\Enrichment\Component'),
];

$config = new Configuration($rules, $finder);

return $config;
