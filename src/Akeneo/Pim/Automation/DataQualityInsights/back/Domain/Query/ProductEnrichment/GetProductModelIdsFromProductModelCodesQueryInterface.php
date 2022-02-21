<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetProductModelIdsFromProductModelCodesQueryInterface
{
    /**
     * @param string[] $productModelCodes
     *
     * @return ProductId[]
     */
    public function execute(array $productModelCodes): array;
}
