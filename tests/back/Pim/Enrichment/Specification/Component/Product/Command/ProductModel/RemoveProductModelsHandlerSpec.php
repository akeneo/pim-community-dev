<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Command\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Command\ProductModel\RemoveProductModelCommand;
use Akeneo\Pim\Enrichment\Component\Product\Command\ProductModel\RemoveProductModelsCommand;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use PhpSpec\ObjectBehavior;

class RemoveProductModelsHandlerSpec extends ObjectBehavior
{
    function let(
        ProductModelRepositoryInterface $productModelRepository,
        BulkRemoverInterface $bulkProductModelRemover
    ) {
        $this->beConstructedWith($productModelRepository, $bulkProductModelRemover, 2);
    }

    function it_removes_product_models_using_entities(BulkRemoverInterface $bulkProductModelRemover)
    {
        $pm1 = new ProductModel();
        $pm1->setCode('pm1');
        $pm2 = new ProductModel();
        $pm2->setCode('pm2');
        $command = RemoveProductModelsCommand::fromProductModels([$pm1, $pm2]);

        $bulkProductModelRemover->removeAll([$pm1, $pm2])->shouldBeCalled();

        $this->__invoke($command);
    }

    function it_removes_product_models_using_commands(
        ProductModelRepositoryInterface $productModelRepository,
        BulkRemoverInterface $bulkProductModelRemover
    ) {
        $pm1 = new ProductModel();
        $pm2 = new ProductModel();
        $command = RemoveProductModelsCommand::fromRemoveProductModelCommands([
            new RemoveProductModelCommand('pm1'),
            new RemoveProductModelCommand('pm2'),
        ]);

        $productModelRepository->findByIdentifiers(['pm1', 'pm2'])->willReturn([$pm1, $pm2]);
        $bulkProductModelRemover->removeAll([$pm1, $pm2])->shouldBeCalled();

        $this->__invoke($command);
    }

    function it_removes_product_models_per_batch_using_commands(
        ProductModelRepositoryInterface $productModelRepository,
        BulkRemoverInterface $bulkProductModelRemover
    ) {
        $pm1 = new ProductModel();
        $pm2 = new ProductModel();
        $pm3 = new ProductModel();
        $command = RemoveProductModelsCommand::fromRemoveProductModelCommands([
            new RemoveProductModelCommand('pm1'),
            new RemoveProductModelCommand('pm2'),
            new RemoveProductModelCommand('pm3'),
        ]);

        $productModelRepository->findByIdentifiers(['pm1', 'pm2'])->willReturn([$pm1, $pm2]);
        $productModelRepository->findByIdentifiers(['pm3'])->willReturn([$pm3]);
        $bulkProductModelRemover->removeAll([$pm1, $pm2])->shouldBeCalled();
        $bulkProductModelRemover->removeAll([$pm3])->shouldBeCalled();

        $this->__invoke($command);
    }
}
