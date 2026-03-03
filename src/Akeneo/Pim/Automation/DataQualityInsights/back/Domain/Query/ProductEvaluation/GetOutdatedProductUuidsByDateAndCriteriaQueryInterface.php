<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetOutdatedProductUuidsByDateAndCriteriaQueryInterface
{
    /**
     * @param string[] $criteria
     */
    public function __invoke(ProductUuidCollection $productUuids, \DateTimeImmutable $evaluationDate, array $criteria): ProductUuidCollection;
}
