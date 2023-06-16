<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ConnectorProductNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ConnectorProductWithUuidNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ValuesNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\ProductValueNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\ProductNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\ProductCreatedAndUpdatedEventDataBuilder;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Platform\Component\Webhook\Context;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Platform\Component\Webhook\EventDataCollection;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Routing\RouterInterface;

class ProductCreatedAndUpdatedEventDataBuilderSpec extends ObjectBehavior
{
    public function let(
        GetConnectorProducts $getConnectorProductsQuery,
        ProductValueNormalizer $productValuesNormalizer,
        RouterInterface $router,
        AttributeRepositoryInterface $attributeRepository
    ): void {
        $connectorProductNormalizer = new ConnectorProductNormalizer(
            new ValuesNormalizer($productValuesNormalizer->getWrappedObject(), $router->getWrappedObject()),
            new DateTimeNormalizer(),
            $attributeRepository->getWrappedObject()
        );
        $productValuesNormalizer->normalize(Argument::type(ReadValueCollection::class), 'standard')->willReturn([]);

        $connectorProductWithUuidNormalizer = new ConnectorProductWithUuidNormalizer(
            new ValuesNormalizer($productValuesNormalizer->getWrappedObject(), $router->getWrappedObject()),
            new DateTimeNormalizer()
        );

        $this->beConstructedWith(
            $getConnectorProductsQuery,
            $connectorProductNormalizer,
            $connectorProductWithUuidNormalizer
        );
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(ProductCreatedAndUpdatedEventDataBuilder::class);
        $this->shouldImplement(EventDataBuilderInterface::class);
    }

    public function it_supports_a_bulk_event_of_product_created_and_updated_events(): void
    {
        $bulkEvent = new BulkEvent([
            new ProductCreated(Author::fromNameAndType('julia', Author::TYPE_UI), [
                'identifier' => '1',
                'uuid' => Uuid::uuid4(),
            ]),
            new ProductUpdated(Author::fromNameAndType('julia', Author::TYPE_UI), [
                'identifier' => '2',
                'uuid' => Uuid::uuid4(),
            ]),
        ]);

        $this->supports($bulkEvent)->shouldReturn(true);
    }

    public function it_does_not_support_a_bulk_event_of_unsupported_product_events(): void
    {
        $bulkEvent = new BulkEvent([
            new ProductCreated(Author::fromNameAndType('julia', Author::TYPE_UI), [
                'identifier' => '1',
                'uuid' => Uuid::uuid4(),
            ]),
            new ProductRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), [
                'identifier' => '1',
                'uuid' => Uuid::uuid4(),
                'category_codes' => [],
            ]),
        ]);

        $this->supports($bulkEvent)->shouldReturn(false);
    }

    public function it_builds_a_bulk_event_of_product_created_and_updated_event(
        GetConnectorProducts $getConnectorProductsQuery,
        AttributeRepositoryInterface $attributeRepository
    ): void {
        $context = new Context('ecommerce_0000', 10, false);
        $attributeRepository->getIdentifierCode()->willReturn('sku');

        $blueJeanUuid = Uuid::uuid4();
        $blueJeanEvent = new ProductCreated(Author::fromNameAndType('julia', Author::TYPE_UI), [
            'identifier' => 'blue_jean',
            'uuid' => $blueJeanUuid,
        ]);
        $redJeanUuid = Uuid::uuid4();
        $redJeanEvent = new ProductUpdated(Author::fromNameAndType('julia', Author::TYPE_UI), [
            'identifier' => 'red_jean',
            'uuid' => $redJeanUuid,
        ]);
        $bulkEvent = new BulkEvent([$blueJeanEvent, $redJeanEvent]);

        $productList = new ConnectorProductList(2, [
            $this->buildConnectorProduct($blueJeanUuid, 'blue_jean'),
            $this->buildConnectorProduct($redJeanUuid, 'red_jean'),
        ]);

        $getConnectorProductsQuery
            ->fromProductUuids([$blueJeanUuid, $redJeanUuid], 10, null, null, null)
            ->willReturn($productList);

        $expectedCollection = new EventDataCollection();
        $expectedCollection->setEventData($blueJeanEvent, [
            'resource' => [
                'uuid' => $blueJeanUuid,
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
                'uuid' => $redJeanUuid,
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

        $collection = $this->build($bulkEvent, $context)->getWrappedObject();

        Assert::assertEquals($expectedCollection, $collection);
    }

    public function it_builds_a_bulk_event_of_product_created_and_updated_event_if_a_product_has_been_removed(
        GetConnectorProducts $getConnectorProductsQuery,
        AttributeRepositoryInterface $attributeRepository
    ): void {
        $context = new Context('ecommerce_0000', 10, false);
        $attributeRepository->getIdentifierCode()->willReturn('sku');

        $blueJeanUuid = Uuid::uuid4();
        $productList = new ConnectorProductList(1, [
            $this->buildConnectorProduct($blueJeanUuid, 'blue_jean')
        ]);
        $redJeanUuid = Uuid::uuid4();

        $getConnectorProductsQuery
            ->fromProductUuids([$blueJeanUuid, $redJeanUuid], 10, null, null, null)
            ->willReturn($productList);

        $blueJeanEvent = new ProductCreated(Author::fromNameAndType('julia', Author::TYPE_UI), [
            'identifier' => 'blue_jean',
            'uuid' => $blueJeanUuid,
        ]);
        $redJeanEvent = new ProductUpdated(Author::fromNameAndType('julia', Author::TYPE_UI), [
            'identifier' => 'red_jean',
            'uuid' => $redJeanUuid,
        ]);
        $bulkEvent = new BulkEvent([$blueJeanEvent, $redJeanEvent]);

        $expectedCollection = new EventDataCollection();
        $expectedCollection->setEventData($blueJeanEvent, [
            'resource' => [
                'uuid' => $blueJeanUuid,
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
        $expectedCollection->setEventDataError($redJeanEvent, new ProductNotFoundException($redJeanUuid));

        $collection = $this->build($bulkEvent, $context)->getWrappedObject();

        Assert::assertEquals($expectedCollection, $collection);
    }

    public function it_builds_a_bulk_event_of_product_created_and_updated_event_using_uuid_normalizer(
        GetConnectorProducts $getConnectorProductsQuery
    ): void {
        $context = new Context('ecommerce_0000', 10, true);

        $blueJeanUuid = Uuid::uuid4();
        $blueJeanEvent = new ProductCreated(Author::fromNameAndType('julia', Author::TYPE_UI), [
            'identifier' => 'blue_jean',
            'uuid' => $blueJeanUuid,
        ]);
        $redJeanUuid = Uuid::uuid4();
        $redJeanEvent = new ProductUpdated(Author::fromNameAndType('julia', Author::TYPE_UI), [
            'identifier' => 'red_jean',
            'uuid' => $redJeanUuid,
        ]);
        $bulkEvent = new BulkEvent([$blueJeanEvent, $redJeanEvent]);

        $productList = new ConnectorProductList(2, [
            $this->buildConnectorProduct($blueJeanUuid, 'blue_jean'),
            $this->buildConnectorProduct($redJeanUuid, 'red_jean'),
        ]);

        $getConnectorProductsQuery
            ->fromProductUuids([$blueJeanUuid, $redJeanUuid], 10, null, null, null)
            ->willReturn($productList);

        $expectedCollection = new EventDataCollection();
        $expectedCollection->setEventData($blueJeanEvent, [
            'resource' => [
                'uuid' => $blueJeanUuid,
                'enabled' => true,
                'family' => null,
                'categories' => [],
                'groups' => [],
                'parent' => null,
                'values' => (object) [],
                'created' => '2020-04-23T15:55:50+00:00',
                'updated' => '2020-04-25T15:55:50+00:00',
                'associations' => (object) [],
                'quantified_associations' => (object) [],
            ],
        ]);
        $expectedCollection->setEventData($redJeanEvent, [
            'resource' => [
                'uuid' => $redJeanUuid,
                'enabled' => true,
                'family' => null,
                'categories' => [],
                'groups' => [],
                'parent' => null,
                'values' => (object) [],
                'created' => '2020-04-23T15:55:50+00:00',
                'updated' => '2020-04-25T15:55:50+00:00',
                'associations' => (object) [],
                'quantified_associations' => (object) [],
            ],
        ]);

        $collection = $this->build($bulkEvent, $context)->getWrappedObject();

        Assert::assertEquals($expectedCollection, $collection);
    }

    private function buildConnectorProduct(UuidInterface $uuid, string $identifier): ConnectorProduct
    {
        return new ConnectorProduct(
            $uuid,
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
            null
        );
    }
}
