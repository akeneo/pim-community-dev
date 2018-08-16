<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product\ContextConfigurator;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ContextConfiguratorSpec extends ObjectBehavior
{
    function let(
        ProductRepositoryInterface $productRepository,
        AttributeRepositoryInterface $attributeRepository,
        RequestParameters $requestParams,
        UserContext $userContext,
        ObjectManager $objectManager,
        GroupRepositoryInterface $productGroupRepository,
        RequestStack $requestStack,
        AttributeGroupAccessRepository $accessRepository
    ) {
        $this->beConstructedWith(
            $productRepository,
            $attributeRepository,
            $requestParams,
            $userContext,
            $objectManager,
            $productGroupRepository,
            $requestStack,
            $accessRepository
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ContextConfigurator::class);
    }

    function it_configures_the_grid_with_tree_id_in_parameters(
        $requestParams,
        $requestStack,
        $userContext,
        $attributeRepository,
        DatagridConfiguration $configuration,
        Request $request,
        UserInterface $user
    ) {
        $this->buildBaseConfiguration($requestParams, $requestStack, $userContext, $attributeRepository, $configuration, $request, $user);

        $requestParams->get('_filter')->willReturn([
            'category' => [
                'value' => [
                    'treeId' => 1
                ]
            ],
            'scope' => [
                'value' => 'ecommerce'
            ]
        ]);

        $configuration->offsetSetByPath('[source][current_tree_id]', 1)->shouldBeCalled();

        $this->configure($configuration)->shouldReturn(null);
    }

    function it_configures_the_grid_with_accessible_tree(
        $requestParams,
        $requestStack,
        $userContext,
        $attributeRepository,
        DatagridConfiguration $configuration,
        Request $request,
        UserInterface $user,
        CategoryInterface $tree
    ) {
        $this->buildBaseConfiguration($requestParams, $requestStack, $userContext, $attributeRepository, $configuration, $request, $user);
        $userContext->getAccessibleUserTree()->willReturn($tree);
        $tree->getId()->willReturn(1);

        $requestParams->get('_filter')->willReturn([
            'category' => [
                'value' => [
                    'treeId' => null
                ]
            ],
            'scope' => [
                'value' => 'ecommerce'
            ]
        ]);

        $configuration->offsetSetByPath('[source][current_tree_id]', 1)->shouldBeCalled();

        $this->configure($configuration)->shouldReturn(null);
    }

    function it_configures_the_grid_without_accessible_tree(
        $requestParams,
        $requestStack,
        $userContext,
        $attributeRepository,
        DatagridConfiguration $configuration,
        Request $request,
        UserInterface $user,
        CategoryInterface $tree
    ) {
        $this->buildBaseConfiguration($requestParams, $requestStack, $userContext, $attributeRepository, $configuration, $request, $user);
        $userContext->getAccessibleUserTree()->willThrow(\LogicException::class);

        $requestParams->get('_filter')->willReturn([
            'category' => [
                'value' => [
                    'treeId' => null
                ]
            ],
            'scope' => [
                'value' => 'ecommerce'
            ]
        ]);

        $configuration->offsetSetByPath('[source][current_tree_id]', 1)->shouldNotBeCalled();

        $this->configure($configuration)->shouldReturn(null);
    }

    private function buildBaseConfiguration(
        RequestParameters $requestParams,
        RequestStack $requestStack,
        UserContext $userContext,
        AttributeRepositoryInterface $attributeRepository,
        DatagridConfiguration $configuration,
        Request $request,
        UserInterface $user): void
    {
        $requestStack->getCurrentRequest()->willReturn($request);
        $userContext->getUser()->willReturn($user);

        $configuration->offsetSetByPath('[source][product_storage]', 'doctrine/orm')->shouldBeCalled();
        $configuration->offsetGetByPath('[source][repository_parameters]', null)->willReturn(null);

        $requestParams->get('dataLocale', null)->willReturn(null);
        $request->get('dataLocale', null)->willReturn(null);
        $user->getCatalogLocale()->willReturn(null);
        $configuration->offsetSetByPath('[source][locale_code]', null)->shouldBeCalled();

        $requestParams->get('dataScope', null)->willReturn(null);
        $request->get('dataScope', null)->willReturn(null);
        $user->getCatalogScope()->willReturn(null);
        $configuration->offsetSetByPath('[source][scope_code]', 'ecommerce')->shouldBeCalled();

        $request->get('group', null)->willReturn(null);
        $requestParams->get('currentGroup', null)->willReturn(null);
        $configuration->offsetSetByPath('[source][current_group_id]', null)->shouldBeCalled();

        $requestParams->get('_parameters', null)->willReturn(null);
        $requestParams->get('associationType', null)->willReturn(null);
        $configuration->offsetSetByPath('[source][association_type_id]', null)->shouldBeCalled();

        $requestParams->get('product', null)->willReturn(null);
        $configuration->offsetSetByPath('[source][current_product]', null)->shouldBeCalled();

        $attributeRepository->getAttributeIdsUseableInGrid(null)->willReturn([]);
        $configuration->offsetSetByPath('[source][displayed_attribute_ids]', [])->shouldBeCalled();
        $configuration->offsetSetByPath('[source][attributes_configuration]', [])->shouldBeCalled();

        $requestParams->get('_pager', null)->willReturn(null);
        $configuration->offsetGetByPath('[options][toolbarOptions][pageSize][default_per_page]', 25)->willReturn(25);
        $configuration->offsetSetByPath('[source][_per_page]', 25)->shouldBeCalled();
        $configuration->offsetSetByPath('[source][from]', 0)->shouldBeCalled();
    }
}
