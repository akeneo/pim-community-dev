<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConnectorProductList
{
    /** @var int total number of products returned by the search without the pagination */
    private $totalNumberOfProducts;

    /** @var ConnectorProduct[] paginated list of products for the connectors */
    private $connectorProducts;

    /**
     * @param int                $totalNumberOfProducts
     * @param ConnectorProduct[] $connectorProducts
     */
    public function __construct(int $totalNumberOfProducts, array $connectorProducts)
    {
        $this->totalNumberOfProducts = $totalNumberOfProducts;
        $this->connectorProducts = (function (ConnectorProduct ...$connectorProducts) {
            return $connectorProducts;
        })(...$connectorProducts);
    }

    public function totalNumberOfProducts(): int
    {
        return $this->totalNumberOfProducts;
    }

    public function connectorProducts(): array
    {
        return $this->connectorProducts;
    }
}
