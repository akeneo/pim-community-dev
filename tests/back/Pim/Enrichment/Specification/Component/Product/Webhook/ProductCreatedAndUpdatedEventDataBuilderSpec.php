<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ConnectorProductNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ValuesNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\ProductValueNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\ProductCreatedAndUpdatedEventDataBuilder;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Routing\RouterInterface;

class ProductCreatedAndUpdatedEventDataBuilderSpec extends ObjectBehavior
{
    public function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        GetConnectorProducts $getConnectorProductsQuery,
        ProductValueNormalizer $productValuesNormalizer,
        RouterInterface $router
    ): void {
        $connectorProductNormalizer = new ConnectorProductNormalizer(
            new ValuesNormalizer($productValuesNormalizer->getWrappedObject(), $router->getWrappedObject()),
            new DateTimeNormalizer()
        );
        $productValuesNormalizer->normalize(Argument::type(ReadValueCollection::class), 'standard')->willReturn([]);

        $this->beConstructedWith($pqbFactory, $getConnectorProductsQuery, $connectorProductNormalizer);
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
        ProductQueryBuilderFactoryInterface $pqbFactory,
        GetConnectorProducts $getConnectorProductsQuery,
        ProductQueryBuilderInterface $pqb
    ): void {
        $bulkEvent = new BulkEvent([
            new ProductCreated(Author::fromNameAndType('julia', Author::TYPE_UI), ['identifier' => 'blue_jean']),
            new ProductUpdated(Author::fromNameAndType('julia', Author::TYPE_UI), ['identifier' => 'red_jean'])
        ]);

        $blueJeanProduct = $this->buildConnectorProduct(1, 'blue_jean');
        $redJeanProduct = $this->buildConnectorProduct(2, 'red_jean');
        $productList = new ConnectorProductList(2, [$blueJeanProduct, $redJeanProduct]);

        $pqbFactory->create(['limit' => 2])->willReturn($pqb);
        $pqb->addFilter('identifier', Operators::IN_LIST, ['blue_jean', 'red_jean'])
            ->willReturn($pqb);
        $getConnectorProductsQuery->fromProductQueryBuilder(
            $pqb,
            10,
            null,
            null,
            null
        )->willReturn($productList);

        $this->build($bulkEvent, 10)->shouldBeLike(
            [
                ['resource' => [
                    'identifier' => 'blue_jean',
                    'created' => '2020-04-23T15:55:50+00:00',
                    'updated' => '2020-04-25T15:55:50+00:00',
                    'enabled' => true,
                    'family' => null,
                    'categories' => [],
                    'groups' => [],
                    'parent' => null,
                    'values' => (object) [],
                    'associations' => (object) [],
                    'quantified_associations' => (object) [],
                ]],
                ['resource' => [
                    'identifier' => 'red_jean',
                    'created' => '2020-04-23T15:55:50+00:00',
                    'updated' => '2020-04-25T15:55:50+00:00',
                    'enabled' => true,
                    'family' => null,
                    'categories' => [],
                    'groups' => [],
                    'parent' => null,
                    'values' => (object) [],
                    'associations' => (object) [],
                    'quantified_associations' => (object) [],
                ]],
            ]
        );
    }

    public function it_builds_a_bulk_event_of_product_created_and_updated_event_if_a_product_as_been_removed(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        GetConnectorProducts $getConnectorProductsQuery,
        ProductQueryBuilderInterface $pqb
    ): void {
        $blueJeanProduct = $this->buildConnectorProduct(1, 'blue_jean');
        $productList = new ConnectorProductList(1, [$blueJeanProduct]);

        $pqbFactory->create(['limit' => 2])->willReturn($pqb);
        $getConnectorProductsQuery->fromProductQueryBuilder(
            $pqb,
            10,
            null,
            null,
            null
        )->willReturn($productList);

        $bulkEvent = new BulkEvent([
            new ProductCreated(Author::fromNameAndType('julia', Author::TYPE_UI), ['identifier' => 'blue_jean']),
            new ProductUpdated(Author::fromNameAndType('julia', Author::TYPE_UI), ['identifier' => 'red_jean'])
        ]);

        $this->build($bulkEvent, 10)->shouldBeLike(
            [
                ['resource' => [
                    'identifier' => 'blue_jean',
                    'created' => '2020-04-23T15:55:50+00:00',
                    'updated' => '2020-04-25T15:55:50+00:00',
                    'enabled' => true,
                    'family' => null,
                    'categories' => [],
                    'groups' => [],
                    'parent' => null,
                    'values' => (object) [],
                    'associations' => (object) [],
                    'quantified_associations' => (object) [],
                ]],
                null,
            ]
        );
    }

    private function buildConnectorProduct(int $id, string $identifier)
    {
        return new ConnectorProduct(
            $id,
            $identifier,
            new \DateTimeImmutable('2020-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2020-04-25 15:55:50', new \DateTimeZone('UTC')),
            true,
            null,
            [],
            [],
            null,
            [],
            [],
            [],
            new ReadValueCollection()
        );
    }
}
