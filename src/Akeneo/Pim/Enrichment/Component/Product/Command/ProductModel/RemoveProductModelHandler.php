<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\Component\Product\Command\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Webmozart\Assert\Assert;

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
