<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Commands;

use Akeneo\Pim\Enrichment\Component\Product\Commands\GroupProductsCommand;
use Akeneo\Pim\Enrichment\Component\Product\Commands\GroupProductsHandler;
use Akeneo\Pim\Enrichment\Component\Product\Model\Group;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetGroupProductIdentifiers;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;

class GroupProductsHandlerSpec extends ObjectBehavior
{
    function let(GetGroupProductIdentifiers $getGroupProductIdentifiers, EntityManager $entityManager, BulkSaverInterface $productSaver) {
        $this->beConstructedWith($getGroupProductIdentifiers,$entityManager,$productSaver);
    }
    function it_is_initializable()
    {
        $this->shouldHaveType(GroupProductsHandler::class);
    }

    function it_can_propagate_product_added_group(GetGroupProductIdentifiers $getGroupProductIdentifiers,  EntityManager $entityManager, BulkSaverInterface $productSaver) {
        $groupProductsCommand = new GroupProductsCommand(1,["productId1", "productId2"] );
        $group = new Group();
        $product2 = new Product();

        $getGroupProductIdentifiers->byGroupId(1)->willReturn(["productId1"]);
        $entityManager->find(Group::class,1)->willReturn($group);
        $entityManager->find(Product::class,"productId2")->willReturn($product2);
        $this->handle($groupProductsCommand);
        $productSaver->saveAll([$product2])->shouldHaveBeenCalled();

    }
    function it_can_propagate_product_removed_group(GetGroupProductIdentifiers $getGroupProductIdentifiers,  EntityManager $entityManager, BulkSaverInterface $productSaver) {
        $groupProductsCommand = new GroupProductsCommand(1,["productId2"] );
        $group = new Group();
        $product2 = new Product();

        $getGroupProductIdentifiers->byGroupId(1)->willReturn(["productId1","productId2"]);
        $entityManager->find(Group::class,1)->willReturn($group);
        $entityManager->find(Product::class,"productId1")->willReturn($product2);
        $this->handle($groupProductsCommand);
        $productSaver->saveAll([$product2])->shouldHaveBeenCalled();

    }

}
