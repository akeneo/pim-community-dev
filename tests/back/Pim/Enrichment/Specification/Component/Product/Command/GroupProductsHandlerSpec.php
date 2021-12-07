<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Command;

use Akeneo\Pim\Enrichment\Component\Product\Command\GroupProductsCommand;
use Akeneo\Pim\Enrichment\Component\Product\Command\GroupProductsHandler;
use Akeneo\Pim\Enrichment\Component\Product\Model\Group;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindProductIdentifiersInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;

class GroupProductsHandlerSpec extends ObjectBehavior
{
    function let(FindProductIdentifiersInterface $getGroupProductIdentifiers, GroupRepositoryInterface $groupRepository, BulkSaverInterface $productSaver, ProductRepositoryInterface $productRepository)
    {
        $this->beConstructedWith($getGroupProductIdentifiers, $groupRepository, $productSaver, $productRepository, 2);
    }

    function it_is_initializable() {
        $this->shouldHaveType(GroupProductsHandler::class);
    }

    function it_can_propagate_product_added_group(FindProductIdentifiersInterface $getGroupProductIdentifiers, GroupRepositoryInterface $groupRepository, BulkSaverInterface $productSaver, ProductRepositoryInterface $productRepository){
        $product2 = new Product();
        $getGroupProductIdentifiers->fromGroupId(1)->willReturn(["productId1"]);
        $groupRepository->find(1)->willReturn(new Group());
        $productRepository->findOneByIdentifier("productId2")->willReturn($product2);

        $this->handle(new GroupProductsCommand(1, ["productId1", "productId2"]));

        $productSaver->saveAll([$product2])->shouldHaveBeenCalled();

    }

    function it_can_propagate_product_removed_group(FindProductIdentifiersInterface $getGroupProductIdentifiers, GroupRepositoryInterface $groupRepository, BulkSaverInterface $productSaver, ProductRepositoryInterface $productRepository){
        $product = new Product();
        $getGroupProductIdentifiers->fromGroupId(1)->willReturn(["productId1", "productId2"]);
        $groupRepository->find(1)->willReturn(new Group());
        $productRepository->findOneByIdentifier("productId1")->willReturn($product);

        $this->handle(new GroupProductsCommand(1, ["productId2"]));

        $productSaver->saveAll([$product])->shouldHaveBeenCalled();
    }

    function it_can_batch_product_commands(FindProductIdentifiersInterface $getGroupProductIdentifiers, GroupRepositoryInterface $groupRepository, BulkSaverInterface $productSaver, ProductRepositoryInterface $productRepository){
        $product1 = new Product();
        $product2 = new Product();
        $product3 = new Product();
        $getGroupProductIdentifiers->fromGroupId(1)->willReturn([]);
        $groupRepository->find(1)->willReturn(new Group());
        $productRepository->findOneByIdentifier("productId1")->willReturn($product1);
        $productRepository->findOneByIdentifier("productId2")->willReturn($product2);
        $productRepository->findOneByIdentifier("productId3")->willReturn($product3);

        $this->handle(new GroupProductsCommand(1, ["productId1", "productId2", "productId3"]));

        $productSaver->saveAll([$product1, $product2])->shouldHaveBeenCalled();
        $productSaver->saveAll([$product3])->shouldHaveBeenCalled();
    }

}
