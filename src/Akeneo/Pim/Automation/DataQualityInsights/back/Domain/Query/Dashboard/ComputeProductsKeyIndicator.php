<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ComputeProductsKeyIndicator
{
    public function getName(): string;

    /**
     * @return array<int, array<string, array<string, bool>>> Enrichment status by product/product-model channel and locale
     *
     * Example of return:
     * [
     *      42 => [
     *          'ecommerce' => [
     *              'en_US' => true,
     *              'fr_FR' => false,
     *          ],
     *          'mobile' => [
     *              'en_US' => true,
     *          ],
     *      ],
     * ]
     */
    public function compute(ProductIdCollection $productIdCollection): array;
}
