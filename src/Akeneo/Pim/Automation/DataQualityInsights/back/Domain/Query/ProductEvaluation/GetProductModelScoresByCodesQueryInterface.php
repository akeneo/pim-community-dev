<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetProductModelScoresByCodesQueryInterface
{
    public function byProductModelCode(string $productModelCode): Read\Scores;

    /**
     * @return array<string, Read\Scores>
     */
    public function byProductModelCodes(array $productModelCodes): array;
}
