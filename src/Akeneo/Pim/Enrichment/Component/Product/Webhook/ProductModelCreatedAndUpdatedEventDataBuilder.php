<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ConnectorProductModelNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\GetConnectorProductModels;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\ProductModelNotFoundException;
use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Platform\Component\Webhook\EventDataCollection;
use Akeneo\UserManagement\Component\Model\UserInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelCreatedAndUpdatedEventDataBuilder implements EventDataBuilderInterface
{
    private ProductQueryBuilderFactoryInterface $pqbFactory;
    private GetConnectorProductModels $getConnectorProductModelsQuery;
    private ConnectorProductModelNormalizer $connectorProductModelNormalizer;

    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        GetConnectorProductModels $getConnectorProductModelsQuery,
        ConnectorProductModelNormalizer $connectorProductModelNormalizer
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->getConnectorProductModelsQuery = $getConnectorProductModelsQuery;
        $this->connectorProductModelNormalizer = $connectorProductModelNormalizer;
    }

    public function supports(object $event): bool
    {
        if (false === $event instanceof BulkEventInterface) {
            return false;
        }

        foreach ($event->getEvents() as $event) {
            if (false === $event instanceof ProductModelCreated && false === $event instanceof ProductModelUpdated) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param BulkEventInterface $bulkEvent
     */
    public function build(object $bulkEvent, UserInterface $user): EventDataCollection
    {
        $productModels = $this->getConnectorProductModels($this->getProductModelCodes($bulkEvent->getEvents()), $user->getId());

        $collection = new EventDataCollection();

        /** @var ProductModelCreated|ProductModelUpdated $event */
        foreach ($bulkEvent->getEvents() as $event) {
            $productModel = $productModels[$event->getCode()] ?? null;

            if (null === $productModel) {
                $collection->setEventDataError($event, new ProductModelNotFoundException($event->getCode()));

                continue;
            }

            $data = [
                'resource' => $this->connectorProductModelNormalizer->normalizeConnectorProductModel($productModel),
            ];
            $collection->setEventData($event, $data);
        }

        return $collection;
    }

    /**
     * @param (ProductModelCreated|ProductModelUpdated)[] $events
     *
     * @return string[]
     */
    private function getProductModelCodes(array $events): array
    {
        $codes = [];
        foreach ($events as $event) {
            $codes[] = $event->getCode();
        }

        return $codes;
    }

    /**
     * @param string[] $codes
     *
     * @return array<string, (ConnectorProductModel|null)>
     */
    private function getConnectorProductModels(array $codes, int $userId): array
    {
        $pqb = $this->pqbFactory
            ->create(['limit' => count($codes)])
            ->addFilter('identifier', Operators::IN_LIST, $codes);

        $result = $this->getConnectorProductModelsQuery
            ->fromProductQueryBuilder($pqb, $userId, null, null, null)
            ->connectorProductModels();

        $products = array_fill_keys($codes, null);
        foreach ($result as $product) {
            $products[$product->code()] = $product;
        }

        return $products;
    }
}
