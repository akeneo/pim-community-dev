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

namespace spec\Akeneo\EnrichedEntity\Domain\Model\Record;

use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use PhpSpec\ObjectBehavior;

class RecordIdentifierSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('create', ['an_enriched_identifier', 'a_record_identifier']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(RecordIdentifier::class);
    }

    public function it_cannot_be_constructed_with_empty_strings()
    {
        $this->beConstructedThrough('create', ['', '']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_should_contain_only_letters_numbers_and_underscores()
    {
        $this->beConstructedThrough('create', ['badId!', 'record_identifier']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();

        $this->beConstructedThrough('create', ['valid_identifier', 'badId!']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_cannot_be_constructed_with_an_empty_string()
    {
        $this->shouldThrow('\InvalidArgumentException')->during('create', ['enriched_entity_identifier', '']);
        $this->shouldThrow('\InvalidArgumentException')->during('create', ['', 'record_code']);
    }

    public function it_cannot_be_constructed_with_a_string_too_long()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('create', [str_repeat('a', 256), 'record_code']);
        $this->shouldThrow(\InvalidArgumentException::class)->during('create', ['enriched_entity_identifier', str_repeat('a', 256)]);
    }

    public function it_is_possible_to_compare_it()
    {
        $sameIdentifier = RecordIdentifier::create(
            'an_enriched_identifier',
            'a_record_identifier'
        );
        $differentIdentifier = RecordIdentifier::create(
            'an_other_enriched_entity_identifier',
            'other_record_identifier'
        );
        $this->equals($sameIdentifier)->shouldReturn(true);
        $this->equals($differentIdentifier)->shouldReturn(false);
    }

    public function it_normalize_itself()
    {
        $this->normalize()->shouldReturn([
            'enriched_entity_identifier' => 'an_enriched_identifier',
            'identifier' => 'a_record_identifier'
        ]);
    }
}
