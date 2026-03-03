<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2023 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyId;

interface GetFamilyIds
{
    /**
     * @param int[] $attributeIds
     * @return \Generator<FamilyId>
     */
    public function fromActivatedAttributeIds(array $attributeIds): \Generator;

    /**
     * @return \Generator<FamilyId>
     */
    public function fromAttributeGroupCode(AttributeGroupCode $attributeGroupCode): \Generator;
}
