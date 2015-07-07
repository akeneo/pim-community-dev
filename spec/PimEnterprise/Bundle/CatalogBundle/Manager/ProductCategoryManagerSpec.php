<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Manager;

use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\CategoryRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductCategoryRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

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
        CategoryRepositoryInterface $categoryRepo,
        CategoryAccessRepository $accessRepo,
        TokenInterface $token,
        User $user
    ) {
        $this->beConstructedWith(
            $productRepo,
            $categoryRepo,
            $securityContext,
            $accessRepo
        );
    }

    function it_gets_product_count_for_granted_trees(
        $securityContext,
        $productRepo,
        $categoryRepo,
        ProductInterface $product,
        CategoryInterface $firstTree,
        CategoryInterface $secondTree,
        CategoryInterface $firstCat,
        CategoryInterface $secondCat
    ) {
        $product->getCategories()->willReturn([$firstCat, $secondCat]);
        $firstCat->getRoot()->willReturn(1);
        $firstTree->getId()->willReturn(1);
        $secondCat->getRoot()->willReturn(2);
        $secondTree->getId()->willReturn(2);

        $categoryRepo->getPath($firstCat)->willReturn([0 => $firstTree, 1 => $firstCat]);
        $securityContext->isGranted(Attributes::VIEW_PRODUCTS, $firstTree)->willReturn(true);
        $securityContext->isGranted(Attributes::VIEW_PRODUCTS, $firstCat)->willReturn(false);

        $categoryRepo->getPath($secondCat)->willReturn([0 => $secondTree, 1 => $secondCat]);
        $securityContext->isGranted(Attributes::VIEW_PRODUCTS, $secondTree)->willReturn(true);
        $securityContext->isGranted(Attributes::VIEW_PRODUCTS, $secondCat)->willReturn(true);

        $categoryRepo->getChildren(null, true, 'created', 'DESC')->willReturn([0 => $firstTree, 1 => $secondTree]);

        $trees = [
            ['tree' => $firstTree, 'productCount' => 0],
            ['tree' => $secondTree, 'productCount' => 1],
        ];
        $this->getProductCountByGrantedTree($product)->shouldReturn($trees);
    }
}
