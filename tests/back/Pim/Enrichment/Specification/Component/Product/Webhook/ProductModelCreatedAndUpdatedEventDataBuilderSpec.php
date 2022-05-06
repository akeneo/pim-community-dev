<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ConnectorProductModelNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ValuesNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\ProductValueNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\GetConnectorProductModels;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\ProductModelNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\ProductModelCreatedAndUpdatedEventDataBuilder;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Platform\Component\Webhook\Context;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Platform\Component\Webhook\EventDataCollection;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelCreatedAndUpdatedEventDataBuilderSpec extends ObjectBehavior
{
    function let(
        GetConnectorProductModels $getConnectorProductModelsQuery,
        ProductValueNormalizer $productValuesNormalizer,
        RouterInterface $router
    ) {
        $connectorProductModelNormalizer = new ConnectorProductModelNormalizer(
            new ValuesNormalizer($productValuesNormalizer->getWrappedObject(), $router->getWrappedObject()),
            new DateTimeNormalizer(),
        );
        $productValuesNormalizer->normalize(Argument::type(ReadValueCollection::class), 'standard')->willReturn([]);

        $this->beConstructedWith($getConnectorProductModelsQuery, $connectorProductModelNormalizer);
    }


    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(ProductModelCreatedAndUpdatedEventDataBuilder::class);
        $this->shouldImplement(EventDataBuilderInterface::class);
    }

    public function it_supports_a_bulk_event_of_product_model_created_and_updated_events(): void
    {
        $bulkEvent = new BulkEvent([
            new ProductModelCreated(Author::fromNameAndType('julia', Author::TYPE_UI), ['code' => '1']),
            new ProductModelUpdated(Author::fromNameAndType('julia', Author::TYPE_UI), ['code' => '2']),
        ]);

        $this->supports($bulkEvent)->shouldReturn(true);
    }

    public function it_does_not_support_a_bulk_event_of_unsupported_product_model_events(): void
    {
        $bulkEvent = new BulkEvent([
            new ProductModelCreated(Author::fromNameAndType('julia', Author::TYPE_UI), ['code' => '1']),
            new ProductModelRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), [
                'code' => '1',
                'category_codes' => [],
            ]),
        ]);

        $this->supports($bulkEvent)->shouldReturn(false);
    }

    public function it_builds_a_bulk_event_of_product_created_and_updated_event(
        GetConnectorProductModels $getConnectorProductModelsQuery
    ): void {
        $context = new Context('ecommerce_0000', 10);

        $jeanEvent = new ProductModelCreated(Author::fromNameAndType('julia', Author::TYPE_UI), [
            'code' => 'jean',
        ]);
        $shoesEvent = new ProductModelUpdated(Author::fromNameAndType('julia', Author::TYPE_UI), [
            'code' => 'shoes',
        ]);
        $bulkEvent = new BulkEvent([$jeanEvent, $shoesEvent]);

        $productModelList = new ConnectorProductModelList(2, [
            $this->buildConnectorProductModel(1, 'jean'),
            $this->buildConnectorProductModel(2, 'shoes'),
        ]);

        $getConnectorProductModelsQuery->fromProductModelCodes(['jean', 'shoes'], 10, null, null, null)
            ->willReturn($productModelList);

        $expectedCollection = new EventDataCollection();
        $expectedCollection->setEventData($jeanEvent, [
            'resource' => [
                'code' => 'jean',
                'family' => 'another_family',
                'family_variant' => 'another_family_variant',
                'parent' => null,
                'categories' => [],
                'values' => (object)[],
                'created' => '2020-04-23T15:55:50+00:00',
                'updated' => '2020-04-25T15:55:50+00:00',
                'associations' => (object)[],
                'quantified_associations' => (object)[],
            ],
        ]);
        $expectedCollection->setEventData($shoesEvent, [
            'resource' => [
                'code' => 'shoes',
                'family' => 'another_family',
                'family_variant' => 'another_family_variant',
                'parent' => null,
                'categories' => [],
                'values' => (object)[],
                'created' => '2020-04-23T15:55:50+00:00',
                'updated' => '2020-04-25T15:55:50+00:00',
                'associations' => (object)[],
                'quantified_associations' => (object)[],
            ],
        ]);

        $collection = $this->build($bulkEvent, $context)->getWrappedObject();

        Assert::assertEquals($expectedCollection, $collection);
    }

    public function it_builds_a_bulk_event_of_product_created_and_updated_event_if_a_product_as_been_removed(
        GetConnectorProductModels $getConnectorProductModelsQuery
    ): void {
        $context = new Context('ecommerce_0000', 10);

        $productList = new ConnectorProductModelList(1, [$this->buildConnectorProductModel(1, 'jean')]);

        $getConnectorProductModelsQuery->fromProductModelCodes(['jean', 'shoes'], 10, null, null, null)
            ->willReturn($productList);

        $jeanEvent = new ProductModelCreated(Author::fromNameAndType('julia', Author::TYPE_UI), [
            'code' => 'jean',
        ]);
        $shoesEvent = new ProductModelUpdated(Author::fromNameAndType('julia', Author::TYPE_UI), [
            'code' => 'shoes',
        ]);
        $bulkEvent = new BulkEvent([$jeanEvent, $shoesEvent]);

        $expectedCollection = new EventDataCollection();
        $expectedCollection->setEventData($jeanEvent, [
            'resource' => [
                'code' => 'jean',
                'family' => 'another_family',
                'family_variant' => 'another_family_variant',
                'parent' => null,
                'categories' => [],
                'values' => (object)[],
                'created' => '2020-04-23T15:55:50+00:00',
                'updated' => '2020-04-25T15:55:50+00:00',
                'associations' => (object)[],
                'quantified_associations' => (object)[],
            ],
        ]);
        $expectedCollection->setEventDataError($shoesEvent, new ProductModelNotFoundException('shoes'));

        $collection = $this->build($bulkEvent, $context)->getWrappedObject();

        Assert::assertEquals($expectedCollection, $collection);
    }

    private function buildConnectorProductModel(int $id, string $code)
    {
        return new ConnectorProductModel(
            $id,
            $code,
            new \DateTimeImmutable('2020-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2020-04-25 15:55:50', new \DateTimeZone('UTC')),
            null,
            'another_family',
            'another_family_variant',
            [],
            [],
            [],
            [],
            new ReadValueCollection(),
            null
        );
    }
}
