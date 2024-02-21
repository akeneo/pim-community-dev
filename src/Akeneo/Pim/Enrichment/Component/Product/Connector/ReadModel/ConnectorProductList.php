<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel;

use Webmozart\Assert\Assert;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConnectorProductList
{
    /**
     * @param int $totalNumberOfProducts total number of products returned by the search without the pagination
     * @param ConnectorProduct[] $connectorProducts paginated list of products for the connectors
     */
    public function __construct(private int $totalNumberOfProducts, private array $connectorProducts)
    {
        Assert::greaterThanEq($this->totalNumberOfProducts, 0);
        Assert::allIsInstanceOf($connectorProducts, ConnectorProduct::class);
        Assert::isList($connectorProducts);
    }

    public function totalNumberOfProducts(): int
    {
        return $this->totalNumberOfProducts;
    }

    /**
     * @return ConnectorProduct[]
     */
    public function connectorProducts(): array
    {
        return $this->connectorProducts;
    }
}
