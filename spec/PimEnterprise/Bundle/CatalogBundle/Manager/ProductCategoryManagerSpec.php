<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Manager;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductCategoryRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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
        AuthorizationCheckerInterface $authorizationChecker,
        ProductCategoryRepositoryInterface $productRepo,
        CategoryRepositoryInterface $categoryRepo,
        CategoryAccessRepository $accessRepo,
        TokenStorageInterface $tokenStorage
    ) {
        $this->beConstructedWith(
            $productRepo,
            $categoryRepo,
            $authorizationChecker,
            $accessRepo,
            $tokenStorage
        );
    }

    function it_gets_product_count_for_granted_trees(
        $authorizationChecker,
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
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $firstTree)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $firstCat)->willReturn(false);

        $categoryRepo->getPath($secondCat)->willReturn([0 => $secondTree, 1 => $secondCat]);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $secondTree)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $secondCat)->willReturn(true);

        $categoryRepo->getChildren(null, true, 'created', 'DESC')->willReturn([0 => $firstTree, 1 => $secondTree]);

        $trees = [
            ['tree' => $firstTree, 'productCount' => 0],
            ['tree' => $secondTree, 'productCount' => 1],
        ];
        $this->getProductCountByGrantedTree($product)->shouldReturn($trees);
    }
}
