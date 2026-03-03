<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetEntityIdsImpactedByAttributeGroupActivationQueryInterface
{
    /**
     * @return \Iterator<int, ProductEntityIdCollection>
     */
    public function updatedSince(\DateTimeImmutable $updatedSince, int $bulkSize): \Iterator;

    /**
     * @return \Iterator<int, ProductEntityIdCollection>
     */
    public function forAttributeGroup(AttributeGroupCode $attributeGroupCode, int $bulkSize): \Iterator;
}
