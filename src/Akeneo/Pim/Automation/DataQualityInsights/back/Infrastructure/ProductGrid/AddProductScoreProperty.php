<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\AddAdditionalProductProperties;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRowsParameters;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AddProductScoreProperty implements AddAdditionalProductProperties
{
    public function __construct(
        private AddScoresToProductAndProductModelRows $addScoresToProductAndProductModelRows
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function add(FetchProductAndProductModelRowsParameters $fetchProductAndProductModelRowsParameters, array $rows): array
    {
        return ($this->addScoresToProductAndProductModelRows)(
            $fetchProductAndProductModelRowsParameters,
            $rows,
            'product'
        );
    }
}
