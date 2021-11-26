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

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Command\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Command\ProductModel\RemoveProductModelCommand;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use PhpSpec\ObjectBehavior;

class RemoveProductModelHandlerSpec extends ObjectBehavior
{
    function let(
        ProductModelRepositoryInterface $productModelRepository,
        RemoverInterface $productModelRemover,
        Client $productAndProductModelClient
    ) {
        $this->beConstructedWith($productModelRepository, $productModelRemover, $productAndProductModelClient);
    }

    function it_removes_the_product_model(
        ProductModelRepositoryInterface $productModelRepository,
        RemoverInterface $productModelRemover,
        Client $productAndProductModelClient
    ) {
        $command = new RemoveProductModelCommand('pm');
        $productModel = new ProductModel();

        $productModelRepository->findOneByIdentifier('pm')->willReturn($productModel);
        $productModelRemover->remove($productModel)->shouldBeCalled();
        $productAndProductModelClient->refreshIndex()->shouldBeCalled();

        $this->__invoke($command);
    }

    function it_throws_an_exception_when_product_model_does_not_exist(
        ProductModelRepositoryInterface $productModelRepository
    ) {
        $command = new RemoveProductModelCommand('pm');
        $productModelRepository->findOneByIdentifier('pm')->willReturn(null);

        $this->shouldThrow(\InvalidArgumentException::class)->during('__invoke', [$command]);
    }
}
