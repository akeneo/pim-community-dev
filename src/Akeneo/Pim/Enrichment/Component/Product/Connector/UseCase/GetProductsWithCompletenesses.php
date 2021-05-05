<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductsWithCompletenesses implements GetProductsWithCompletenessesInterface
{
    private GetProductCompletenesses $getProductCompletenesses;

    public function __construct(GetProductCompletenesses $getProductCompletenesses)
    {
        $this->getProductCompletenesses = $getProductCompletenesses;
    }

    public function fromConnectorProduct(ConnectorProduct $product): ConnectorProduct
    {
        return $product->buildWithCompletenesses($this->getProductCompletenesses->fromProductId($product->id()));
    }
}
