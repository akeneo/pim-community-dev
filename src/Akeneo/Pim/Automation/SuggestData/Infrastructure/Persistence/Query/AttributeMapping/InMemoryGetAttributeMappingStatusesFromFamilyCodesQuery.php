<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Query\AttributeMapping;

use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\Family;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Query\GetAttributeMappingStatusesFromFamilyCodesQueryInterface;

/**
 * In-memory implementation of the GetAttributeMappingStatusesFromFamilyCodesQuery query.
 * As this status is not used in the acceptance tests, it always returns that mapping is pending.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class InMemoryGetAttributeMappingStatusesFromFamilyCodesQuery implements GetAttributeMappingStatusesFromFamilyCodesQueryInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(array $familyCodes): array
    {
        $mappingStatus = array_fill_keys($familyCodes, Family::MAPPING_PENDING);

        return $mappingStatus;
    }
}
