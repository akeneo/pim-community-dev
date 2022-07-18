<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetProductScoresQueryInterface
{
    /**
     * @param UuidInterface[] $productUuids
     * @return QualityScoreCollection[]
     */
    public function byProductUuids(array $productUuids): array;

    public function byProductUuid(UuidInterface $productUuid): QualityScoreCollection;
}
