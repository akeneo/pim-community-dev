<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetProductScoresQueryInterface
{
    /**
     * @param string[] $productIdentifiers
     * @return QualityScoreCollection[]
     */
    public function byProductIdentifiers(array $productIdentifiers): array;

    public function byProductIdentifier(string $productIdentifier): QualityScoreCollection;
}
