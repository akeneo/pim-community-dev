<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Commands;

use Akeneo\Pim\Enrichment\Component\Product\Commands\GroupProductsCommand;
use Akeneo\Pim\Enrichment\Component\Product\Commands\GroupProductsHandler;
use Akeneo\Pim\Enrichment\Component\Product\Model\Group;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindProductIdentifiersInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;

class GroupProductsHandlerSpec extends ObjectBehavior
{
    function let(FindProductIdentifiersInterface $getGroupProductIdentifiers, GroupRepositoryInterface $groupRepository, BulkSaverInterface $productSaver, ProductRepositoryInterface $productRepository) {
        $this->beConstructedWith($getGroupProductIdentifiers,$groupRepository,$productSaver, $productRepository);
    }
    function it_is_initializable()
    {
        $this->shouldHaveType(GroupProductsHandler::class);
    }

    function it_can_propagate_product_added_group(FindProductIdentifiersInterface $getGroupProductIdentifiers, GroupRepositoryInterface $groupRepository, BulkSaverInterface $productSaver, ProductRepositoryInterface $productRepository) {
        $groupProductsCommand = new GroupProductsCommand(1,["productId1", "productId2"] );
        $group = new Group();
        $product2 = new Product();

        $getGroupProductIdentifiers->fromGroupId(1)->willReturn(["productId1"]);
        $groupRepository->find(1)->willReturn($group);
        $productRepository->find("productId2")->willReturn($product2);
        $this->handle($groupProductsCommand);
        $productSaver->saveAll([$product2])->shouldHaveBeenCalled();

    }

    function it_can_propagate_product_removed_group(FindProductIdentifiersInterface $getGroupProductIdentifiers, GroupRepositoryInterface $groupRepository, BulkSaverInterface $productSaver, ProductRepositoryInterface $productRepository) {
        $groupProductsCommand = new GroupProductsCommand(1,["productId2"] );
        $group = new Group();
        $product2 = new Product();

        $getGroupProductIdentifiers->fromGroupId(1)->willReturn(["productId1","productId2"]);
        $groupRepository->find(1)->willReturn($group);
        $productRepository->findOneByIdentifier("productId1")->willReturn($product2);
        $this->handle($groupProductsCommand);
        $productSaver->saveAll([$product2])->shouldHaveBeenCalled();

    }

}
