<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\ProductNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\ProductCreatedAndUpdatedEventDataBuilder;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductCreatedAndUpdatedEventDataBuilderSpec extends ObjectBehavior
{
    public function let(
        IdentifiableObjectRepositoryInterface $productRepository,
        NormalizerInterface $externalApiNormalizer
    ): void {
        $this->beConstructedWith($productRepository, $externalApiNormalizer);
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(ProductCreatedAndUpdatedEventDataBuilder::class);
        $this->shouldImplement(EventDataBuilderInterface::class);
    }

    public function it_supports_product_created_event(): void
    {
        $this->supports(new ProductCreated('julia', 'ui', ['data']))->shouldReturn(true);
    }

    public function it_supports_product_updated_event(): void
    {
        $this->supports(new ProductUpdated('julia', 'ui', ['data']))->shouldReturn(true);
    }

    public function it_does_not_supports_other_business_event(): void
    {
        $this->supports(new ProductRemoved('julia', 'ui', ['data']))->shouldReturn(false);
    }


    public function it_builds_product_created_event($productRepository, $externalApiNormalizer): void
    {
        $product = new Product();
        $product->setId(1);
        $product->setIdentifier('product_identifier');

        $productRepository->findOneByIdentifier('product_identifier')->willReturn($product);
        $externalApiNormalizer->normalize($product, 'external_api')->willReturn(
            [
                'identifier' => 'product_identifier',
            ]
        );

        $this->build(new ProductCreated('julia', 'ui', ['identifier' => 'product_identifier']))->shouldReturn(
            [
                'resource' => ['identifier' => 'product_identifier'],
            ]
        );
    }

    public function it_builds_product_updated_event($productRepository, $externalApiNormalizer): void
    {
        $product = new Product();
        $product->setId(1);
        $product->setIdentifier('product_identifier');

        $productRepository->findOneByIdentifier('product_identifier')->willReturn($product);
        $externalApiNormalizer->normalize($product, 'external_api')->willReturn(
            [
                'identifier' => 'product_identifier',
            ]
        );

        $this->build(new ProductUpdated('julia', 'ui', ['identifier' => 'product_identifier']))->shouldReturn(
            [
                'resource' => ['identifier' => 'product_identifier'],
            ]
        );
    }

    public function it_does_not_build_other_business_event(): void
    {
        $product = new Product();
        $product->setId(1);
        $product->setIdentifier('product_identifier');

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('build', [new ProductRemoved('julia', 'ui', ['identifier' => 'product_identifier'])]);
    }

    public function it_does_not_build_if_product_was_not_found($productRepository): void
    {
        $productRepository->findOneByIdentifier('product_identifier')->willReturn(null);

        $this->shouldThrow(ProductNotFoundException::class)
            ->during('build', [new ProductCreated('julia', 'ui', ['identifier' => 'product_identifier'])]);
    }
}
