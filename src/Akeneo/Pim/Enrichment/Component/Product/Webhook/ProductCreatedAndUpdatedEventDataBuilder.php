<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ConnectorProductNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ConnectorProductWithUuidNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\ProductNotFoundException;
use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\Webhook\Context;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Platform\Component\Webhook\EventDataCollection;
use Ramsey\Uuid\UuidInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCreatedAndUpdatedEventDataBuilder implements EventDataBuilderInterface
{
    public function __construct(
        private GetConnectorProducts $getConnectorProductsQuery,
        private ConnectorProductNormalizer $connectorProductNormalizer,
        private ConnectorProductWithUuidNormalizer $connectorProductWithUuidNormalizer,
    ) {
    }

    public function supports(BulkEventInterface $event): bool
    {
        if (false === $event instanceof BulkEventInterface) {
            return false;
        }

        foreach ($event->getEvents() as $event) {
            if (false === $event instanceof ProductCreated && false === $event instanceof ProductUpdated) {
                return false;
            }
        }

        return true;
    }

    public function build(BulkEventInterface $bulkEvent, Context $context): EventDataCollection
    {
        $products = $this->getConnectorProducts(
            \array_map(
                static fn (ProductCreated|ProductUpdated $event): UuidInterface => $event->getProductUuid(),
                $bulkEvent->getEvents()
            ),
            $context->getUserId()
        );

        $collection = new EventDataCollection();

        /** @var ProductCreated|ProductUpdated $event */
        foreach ($bulkEvent->getEvents() as $event) {
            $product = $products[$event->getProductUuid()->toString()] ?? null;

            if (null === $product) {
                $collection->setEventDataError($event, new ProductNotFoundException($event->getProductUuid()));

                continue;
            }

            $normalizer = $context->isUsingUuid()
                ? $this->connectorProductWithUuidNormalizer
                : $this->connectorProductNormalizer;

            $data = [
                'resource' => $normalizer->normalizeConnectorProduct($product),
            ];
            $collection->setEventData($event, $data);
        }

        return $collection;
    }

    /**
     * @param UuidInterface[] $uuids
     *
     * @return array<string, ConnectorProduct>
     */
    private function getConnectorProducts(array $uuids, int $userId): array
    {
        $result = $this->getConnectorProductsQuery
            ->fromProductUuids($uuids, $userId, null, null, null)
            ->connectorProducts();

        $products = [];
        foreach ($result as $product) {
            $products[$product->uuid()->toString()] = $product;
        }

        return $products;
    }
}
