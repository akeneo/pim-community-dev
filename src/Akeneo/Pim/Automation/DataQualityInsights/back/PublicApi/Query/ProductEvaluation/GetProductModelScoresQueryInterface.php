<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetProductModelScoresQueryInterface
{
    /**
     * @param string[] $productModelCodes
     * @return QualityScoreCollection[]
     */
    public function byProductModelCodes(array $productModelCodes): array;

    public function byProductModelCode(string $productModelCode): QualityScoreCollection;
}
