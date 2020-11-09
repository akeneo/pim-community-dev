<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\ProductNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\ProductCreatedAndUpdatedEventDataBuilder;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
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

    public function it_supports_a_bulk_event_of_product_created_and_updated_events(): void
    {
        $bulkEvent = new BulkEvent([
            new ProductCreated(Author::fromNameAndType('julia', Author::TYPE_UI), ['identifier' => '1']),
            new ProductUpdated(Author::fromNameAndType('julia', Author::TYPE_UI), ['identifier' => '2'])
        ]);

        $this->supports($bulkEvent)->shouldReturn(true);
    }

    public function it_does_not_support_a_bulk_event_of_unsupported_product_events(): void
    {
        $bulkEvent = new BulkEvent([
            new ProductCreated(Author::fromNameAndType('julia', Author::TYPE_UI), ['identifier' => '1']),
            new ProductRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), [])
        ]);

        $this->supports($bulkEvent)->shouldReturn(false);
    }

    public function it_does_not_support_an_individual_event(): void
    {
        $event = new ProductUpdated(Author::fromNameAndType('julia', Author::TYPE_UI), ['identifier' => '1']);

        $this->supports($event)->shouldReturn(false);
    }

    public function it_builds_a_bulk_event_of_product_created_and_updated_event(
        $productRepository,
        $externalApiNormalizer
    ): void {
        // TODO mock get products of GetConnectorProduct and normalize

        $bulkEvent = new BulkEvent([
            new ProductCreated(Author::fromNameAndType('julia', Author::TYPE_UI), ['identifier' => '1']),
            new ProductUpdated(Author::fromNameAndType('julia', Author::TYPE_UI), ['identifier' => '2'])
        ]);

        $this->build($bulkEvent)->shouldReturn(
            [
                ['resource' => ['identifier' => '1']],
                ['resource' => ['identifier' => '2']],
            ]
        );
    }

    public function it_does_not_build_if_product_was_not_found(
        $productRepository
    ): void {
        // TODO mock by GetConnectorProduct

        $bulkEvent = new BulkEvent([
            new ProductCreated(Author::fromNameAndType('julia', Author::TYPE_UI), ['identifier' => '1']),
        ]);

        $this->shouldThrow(ProductNotFoundException::class)
            ->during('build', [$bulkEvent]);
    }
}
