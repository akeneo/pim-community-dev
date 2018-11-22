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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Query\AttributeMapping;

use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Query\GetAttributeMappingStatusesFromFamilyCodesQueryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Query\AttributeMapping\InMemoryGetAttributeMappingStatusesFromFamilyCodesQuery;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class InMemoryGetAttributeMappingStatusesFromFamilyCodesQuerySpec extends ObjectBehavior
{
    public function it_is_a_get_attribute_mapping_statuses_for_families_query(): void
    {
        $this->shouldImplement(GetAttributeMappingStatusesFromFamilyCodesQueryInterface::class);
    }

    public function it_is_an_in_memory_implementation_of_get_attribute_mapping_statuses_for_families_query(): void
    {
        $this->shouldBeAnInstanceOf(InMemoryGetAttributeMappingStatusesFromFamilyCodesQuery::class);
    }

    public function it_always_returns_that_there_are_pending_attributes(): void
    {
        $this->execute(['a_family_code', 'another_family_code'])->shouldReturn([
            'a_family_code' => 0,
            'another_family_code' => 0,
        ]);
    }
}
