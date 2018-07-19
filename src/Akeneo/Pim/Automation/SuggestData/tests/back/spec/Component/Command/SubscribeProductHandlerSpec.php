<?php

declare(strict_types=1);

namespace spec\Akeneo\Pim\Automation\SuggestData\Component\Command;

use Akeneo\Pim\Automation\SuggestData\Component\Command\SubscribeProduct;
use Akeneo\Pim\Automation\SuggestData\Component\Command\SubscribeProductHandler;
use Akeneo\Pim\Automation\SuggestData\Component\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Component\Repository\IdentifiersMappingRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;

class SubscribeProductHandlerSpec extends ObjectBehavior
{
    function let(
        ProductRepositoryInterface $productRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository
    ) {
        $this->beConstructedWith($productRepository, $identifiersMappingRepository);
    }

    function it_is_a_subscribe_product_handler()
    {
        $this->shouldHaveType(SubscribeProductHandler::class);
    }

    function it_throws_an_exception_if_the_product_does_not_exist(
        $productRepository,
        $identifiersMappingRepository,
        SubscribeProduct $command,
        IdentifiersMapping $identifiersMapping)
    {
        $identifiersMappingRepository->find()->willReturn($identifiersMapping);
        $identifiersMapping->isEmpty()->willReturn(false);

        $productId = 42;
        $command->getProductId()->willReturn($productId);
        $productRepository->find($productId)->willReturn(null);

        $this->shouldThrow(
            new \Exception(
                sprintf('Could not find product with id "%s"', $productId)
            )
        )->during('handle', [$command]);
    }

    function it_throws_an_exception_if_the_identifiers_mapping_is_empty(
        $identifiersMappingRepository,
        SubscribeProduct $command,
        IdentifiersMapping $identifierMapping
    ) {
        $identifiersMappingRepository->find()->willReturn($identifierMapping);
        $identifierMapping->isEmpty()->willReturn(true);

        $this
            ->shouldThrow(new \Exception('Identifiers mapping has not identifier defined'))
            ->during('handle', [$command]);
    }
}
