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
use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\ProductNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\ProductCreatedAndUpdatedEventDataBuilder;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Platform\Component\Webhook\EventDataCollection;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
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
            new DateTimeNormalizer(),
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
            new ProductUpdated(Author::fromNameAndType('julia', Author::TYPE_UI), ['identifier' => '2']),
        ]);

        $this->supports($bulkEvent)->shouldReturn(true);
    }

    public function it_does_not_support_a_bulk_event_of_unsupported_product_events(): void
    {
        $bulkEvent = new BulkEvent([
            new ProductCreated(Author::fromNameAndType('julia', Author::TYPE_UI), ['identifier' => '1']),
            new ProductRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), [
                'identifier' => '1',
                'category_codes' => [],
            ]),
        ]);

        $this->supports($bulkEvent)->shouldReturn(false);
    }

    public function it_builds_a_bulk_event_of_product_created_and_updated_event(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        GetConnectorProducts $getConnectorProductsQuery,
        ProductQueryBuilderInterface $pqb
    ): void {
        $user = new User();
        $user->setId(10);

        $blueJeanEvent = new ProductCreated(Author::fromNameAndType('julia', Author::TYPE_UI), [
            'identifier' => 'blue_jean',
        ]);
        $redJeanEvent = new ProductUpdated(Author::fromNameAndType('julia', Author::TYPE_UI), [
            'identifier' => 'red_jean',
        ]);
        $bulkEvent = new BulkEvent([$blueJeanEvent, $redJeanEvent]);

        $productList = new ConnectorProductList(2, [
            $this->buildConnectorProduct(1, 'blue_jean'),
            $this->buildConnectorProduct(2, 'red_jean'),
        ]);

        $pqbFactory->create(['limit' => 2])->willReturn($pqb);
        $pqb->addFilter('identifier', Operators::IN_LIST, ['blue_jean', 'red_jean'])->willReturn($pqb);
        $getConnectorProductsQuery->fromProductQueryBuilder($pqb, 10, null, null, null)->willReturn($productList);

        $expectedCollection = new EventDataCollection();
        $expectedCollection->setEventData($blueJeanEvent, [
            'resource' => [
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
            ],
        ]);
        $expectedCollection->setEventData($redJeanEvent, [
            'resource' => [
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
            ],
        ]);

        $collection = $this->build($bulkEvent, $user)->getWrappedObject();

        Assert::assertEquals($expectedCollection, $collection);
    }

    public function it_builds_a_bulk_event_of_product_created_and_updated_event_if_a_product_as_been_removed(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        GetConnectorProducts $getConnectorProductsQuery,
        ProductQueryBuilderInterface $pqb
    ): void {
        $user = new User();
        $user->setId(10);

        $productList = new ConnectorProductList(1, [$this->buildConnectorProduct(1, 'blue_jean')]);

        $pqbFactory->create(['limit' => 2])->willReturn($pqb);
        $pqb->addFilter('identifier', Operators::IN_LIST, ['blue_jean', 'red_jean'])->willReturn($pqb);
        $getConnectorProductsQuery->fromProductQueryBuilder($pqb, 10, null, null, null)->willReturn($productList);

        $blueJeanEvent = new ProductCreated(Author::fromNameAndType('julia', Author::TYPE_UI), [
            'identifier' => 'blue_jean',
        ]);
        $redJeanEvent = new ProductUpdated(Author::fromNameAndType('julia', Author::TYPE_UI), [
            'identifier' => 'red_jean',
        ]);
        $bulkEvent = new BulkEvent([$blueJeanEvent, $redJeanEvent]);

        $expectedCollection = new EventDataCollection();
        $expectedCollection->setEventData($blueJeanEvent, [
            'resource' => [
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
            ],
        ]);
        $expectedCollection->setEventDataError($redJeanEvent, new ProductNotFoundException('red_jean'));

        $collection = $this->build($bulkEvent, $user)->getWrappedObject();

        Assert::assertEquals($expectedCollection, $collection);
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
            new ReadValueCollection(),
            null,
        );
    }
}
