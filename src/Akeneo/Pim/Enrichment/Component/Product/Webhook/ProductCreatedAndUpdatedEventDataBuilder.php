<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ConnectorProductNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\ProductNotFoundException;
use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Platform\Component\Webhook\EventDataCollection;
use Akeneo\UserManagement\Component\Model\UserInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCreatedAndUpdatedEventDataBuilder implements EventDataBuilderInterface
{
    private ProductQueryBuilderFactoryInterface $pqbFactory;
    private GetConnectorProducts $getConnectorProductsQuery;
    private ConnectorProductNormalizer $connectorProductNormalizer;

    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        GetConnectorProducts $getConnectorProductsQuery,
        ConnectorProductNormalizer $connectorProductNormalizer
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->getConnectorProductsQuery = $getConnectorProductsQuery;
        $this->connectorProductNormalizer = $connectorProductNormalizer;
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

    public function build(BulkEventInterface $bulkEvent, UserInterface $user): EventDataCollection
    {
        $products = $this->getConnectorProducts($this->getProductIdentifiers($bulkEvent->getEvents()), $user->getId());

        $collection = new EventDataCollection();

        /** @var ProductCreated|ProductUpdated $event */
        foreach ($bulkEvent->getEvents() as $event) {
            $product = $products[$event->getIdentifier()] ?? null;

            if (null === $product) {
                $collection->setEventDataError($event, new ProductNotFoundException($event->getIdentifier()));

                continue;
            }

            $data = [
                'resource' => $this->connectorProductNormalizer->normalizeConnectorProduct($product),
            ];
            $dataVersion = sprintf('%s%s', $product->identifier(), $product->updatedDate()->getTimestamp());

            $collection->setEventData($event, $data, $dataVersion);
        }

        return $collection;
    }

    /**
     * @param (ProductCreated|ProductUpdated)[] $events
     *
     * @return string[]
     */
    private function getProductIdentifiers(array $events): array
    {
        $identifiers = [];
        foreach ($events as $event) {
            $identifiers[] = $event->getIdentifier();
        }

        return $identifiers;
    }

    /**
     * @param string[] $identifiers
     *
     * @return array<string, (ConnectorProduct|null)>
     */
    private function getConnectorProducts(array $identifiers, int $userId): array
    {
        $pqb = $this->pqbFactory
            ->create(['limit' => count($identifiers)])
            ->addFilter('identifier', Operators::IN_LIST, $identifiers);

        $result = $this->getConnectorProductsQuery
            ->fromProductQueryBuilder($pqb, $userId, null, null, null)
            ->connectorProducts();

        $products = array_fill_keys($identifiers, null);
        foreach ($result as $product) {
            $products[$product->identifier()] = $product;
        }

        return $products;
    }
}
