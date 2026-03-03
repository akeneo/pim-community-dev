<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Command\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveProductModelsHandler
{
    private ProductModelRepositoryInterface $productModelRepository;
    private BulkRemoverInterface $bulkProductModelRemover;
    private int $batchSize;

    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        BulkRemoverInterface $bulkProductModelRemover,
        int $batchSize
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->bulkProductModelRemover = $bulkProductModelRemover;
        $this->batchSize = $batchSize;
    }

    public function __invoke(RemoveProductModelsCommand $command): void
    {
        // If product models are provided we can directly remove them.
        $productModels = $command->productModels();
        if (null !== $productModels) {
            $this->bulkProductModelRemover->removeAll($productModels);

            return;
        }

        $batchedProductModels = $this->getBatchedProductModelsFromCommand($command);
        foreach ($batchedProductModels as $productModels) {
            $this->bulkProductModelRemover->removeAll($productModels);
        }
    }

    private function getBatchedProductModelsFromCommand(RemoveProductModelsCommand $command)
    {
        $batchedCommands = \array_chunk($command->removeProductModelCommands(), $this->batchSize);

        foreach ($batchedCommands as $commands) {
            $productModelCodes = \array_map(
                static fn (RemoveProductModelCommand $command): string => $command->productModelCode(),
                $commands
            );
            yield $this->productModelRepository->findByIdentifiers($productModelCodes);
        }
    }
}
