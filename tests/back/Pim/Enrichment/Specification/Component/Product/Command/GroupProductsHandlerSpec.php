<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Command;

use Akeneo\Pim\Enrichment\Component\Product\Command\GroupProductsCommand;
use Akeneo\Pim\Enrichment\Component\Product\Command\GroupProductsHandler;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindProductUuidsInGroup;
use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class GroupProductsHandlerSpec extends ObjectBehavior
{
    function let(
        FindProductUuidsInGroup $findProductUuids,
        GroupRepositoryInterface $groupRepository,
        BulkSaverInterface $productSaver,
        ProductRepositoryInterface $productRepository
    ) {
        $this->beConstructedWith($findProductUuids, $groupRepository, $productSaver, $productRepository, 2);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GroupProductsHandler::class);
    }

    function it_can_add_products_to_a_group(
        FindProductUuidsInGroup $findProductUuids,
        GroupRepositoryInterface $groupRepository,
        BulkSaverInterface $productSaver,
        ProductRepositoryInterface $productRepository,
        ProductInterface $productToAddInGroup,
        GroupInterface $group
    ) {
        $alreadyInGroupUuid = Uuid::uuid4();
        $productToAddInGroupUuid = Uuid::uuid4();

        $findProductUuids->forGroupId(1)->shouldBeCalled()->willReturn([$alreadyInGroupUuid->toString()]);
        $groupRepository->find(1)->willReturn($group);
        $productRepository->getItemsFromUuids([$productToAddInGroupUuid->toString()])
                          ->shouldBeCalled()->willReturn([$productToAddInGroup]);

        $productToAddInGroup->addGroup($group)->shouldBeCalled();
        $productSaver->saveAll([$productToAddInGroup])->shouldBeCalledOnce();

        $this->handle(
            new GroupProductsCommand(1, [$alreadyInGroupUuid->toString(), $productToAddInGroupUuid->toString()])
        );
    }

    function it_removes_products_from_a_group(
        FindProductUuidsInGroup $findProductUuids,
        GroupRepositoryInterface $groupRepository,
        BulkSaverInterface $productSaver,
        ProductRepositoryInterface $productRepository,
        ProductInterface $productToRemoveFromGroup,
        GroupInterface $group
    ) {
        $alreadyInGroupUuid = Uuid::uuid4();
        $productToRemoveFromGroupUuid = Uuid::uuid4();

        $findProductUuids->forGroupId(1)->shouldBeCalled()->willReturn([
            $alreadyInGroupUuid->toString(),
            $productToRemoveFromGroupUuid->toString(),
        ]);
        $groupRepository->find(1)->willReturn($group);
        $productRepository->getItemsFromUuids([$productToRemoveFromGroupUuid->toString()])
                          ->shouldBeCalled()->willReturn([$productToRemoveFromGroup]);

        $productToRemoveFromGroup->removeGroup($group)->shouldBeCalled();
        $productSaver->saveAll([$productToRemoveFromGroup])->shouldBeCalledOnce();

        $this->handle(
            new GroupProductsCommand(1, [$alreadyInGroupUuid->toString()])
        );
    }

    function it_can_batch_product_commands(
        FindProductUuidsInGroup $findProductUuids,
        GroupRepositoryInterface $groupRepository,
        BulkSaverInterface $productSaver,
        ProductRepositoryInterface $productRepository,
        GroupInterface $group,
        ProductInterface $product1,
        ProductInterface $product3,
        ProductInterface $product4,
    ) {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();
        $uuid4 = Uuid::uuid4();

        $groupRepository->find(1)->willReturn($group);
        $findProductUuids->forGroupId(1)->shouldBeCalled()->willReturn([
            $uuid2->toString(),
            $uuid4->toString(),
        ]);

        $productRepository->getItemsFromUuids([$uuid1->toString(), $uuid3->toString()])
            ->shouldBeCalled()->willReturn([$product1, $product3]);
        $product1->addGroup($group)->shouldBeCalled();
        $product3->addGroup($group)->shouldBeCalled();
        $productSaver->saveAll([$product1, $product3])->shouldBeCalled();

        $productRepository->getItemsFromUuids([$uuid4->toString()])
                          ->shouldBeCalled()->willReturn([$product4]);
        $product4->removeGroup($group)->shouldBeCalled();
        $productSaver->saveAll([$product4])->shouldBeCalled();

        $this->handle(new GroupProductsCommand(1, [
            $uuid1->toString(),
            $uuid2->toString(),
            $uuid3->toString(),
        ]));
    }

}
