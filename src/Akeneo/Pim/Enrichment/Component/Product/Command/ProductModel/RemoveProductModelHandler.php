<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Command\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveProductModelHandler
{
    private ProductModelRepositoryInterface $productModelRepository;
    private RemoverInterface $productModelRemover;
    private Client $productAndProductModelClient;

    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        RemoverInterface $productModelRemover,
        Client $productAndProductModelClient
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->productModelRemover = $productModelRemover;
        $this->productAndProductModelClient = $productAndProductModelClient;
    }

    public function __invoke(RemoveProductModelCommand $command): void
    {
        $productModel = $this->productModelRepository->findOneByIdentifier($command->productModelCode());
        Assert::notNull($productModel);
        $this->productModelRemover->remove($productModel);
        $this->productAndProductModelClient->refreshIndex();
    }
}
