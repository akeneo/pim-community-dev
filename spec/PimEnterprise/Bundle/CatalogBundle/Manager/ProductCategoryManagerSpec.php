<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Manager;

use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use Pim\Bundle\CatalogBundle\Repository\ProductCategoryRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

class ProductCategoryManagerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogBundle\Manager\ProductCategoryManager');
    }

    function it_is_a_ProductCategoryManager()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Manager\ProductCategoryManager');
    }

    function let(
        SecurityContextInterface $securityContext,
        ProductCategoryRepositoryInterface $productRepo,
        CategoryRepository $categoryRepo,
        TokenInterface $token,
        User $user
    ) {
        $securityContext->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $this->beConstructedWith(
            $productRepo,
            $categoryRepo,
            $securityContext
        );
    }

    function it_get_product_count_for_accessible_trees(
        $securityContext,
        $productRepo,
        ProductInterface $product,
        CategoryInterface $firstTree,
        CategoryInterface $secondTree,
        CategoryInterface $thirdTree
    ) {
        $trees = [
            ['tree' => $firstTree, 'productCount' => 21],
            ['tree' => $secondTree, 'productCount' => 4],
            ['tree' => $thirdTree, 'productCount' => 46],
        ];

        $productRepo->getProductCountByTree($product)
            ->shouldBeCalled()
            ->willReturn($trees);

        $securityContext->isGranted(Attributes::VIEW_PRODUCTS, $trees[0]['tree'])->shouldBeCalled()->willReturn(false);
        $securityContext->isGranted(Attributes::VIEW_PRODUCTS, $trees[1]['tree'])->shouldBeCalled()->willReturn(true);
        $securityContext->isGranted(Attributes::VIEW_PRODUCTS, $trees[2]['tree'])->shouldBeCalled()->willReturn(false);

        unset($trees[0]);
        unset($trees[2]);

        $this->getProductCountByTree($product)->shouldReturn($trees);
    }
}
