<?php

declare(strict_types=1);

/*
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\Test\Acceptance\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetExistingReferenceDataCodes;
use Akeneo\Test\Acceptance\Attribute\InMemoryGetExistingReferenceDataCodes;
use PhpSpec\ObjectBehavior;

final class InMemoryGetExistingReferenceDataCodesSpec extends ObjectBehavior
{
    function it_is_a_query_to_get_existing_reference_data_codes()
    {
        $this->shouldImplement(GetExistingReferenceDataCodes::class);
    }

    function it_is_an_in_memory_query()
    {
        $this->shouldBeAnInstanceOf(InMemoryGetExistingReferenceDataCodes::class);
    }

    function it_returns_reference_data_codes_for_reference_data_name()
    {
        $this->add('color', 'purple');
        $this->add('akeneoonboardersupplier', 'michel');

        $this->fromReferenceDataNameAndCodes('akeneoonboardersupplier', ['michel', 'purple'])->shouldReturn(['michel']);
        $this->fromReferenceDataNameAndCodes('color', ['michel', 'purple'])->shouldReturn(['purple']);
    }

    function it_returns_an_empty_array_if_there_is_no_reference_data_code_for_the_given_reference_data_name()
    {
        $this->fromReferenceDataNameAndCodes('akeneoonboardersupplier', ['michel'])->shouldReturn([]);
    }
}
