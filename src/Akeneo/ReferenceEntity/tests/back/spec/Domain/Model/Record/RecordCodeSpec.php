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

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Record;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use PhpSpec\ObjectBehavior;

class RecordCodeSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromString', ['an_identifier']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(RecordCode::class);
    }

    public function it_can_be_transformed_into_a_string()
    {
        $this->__toString()->shouldReturn('an_identifier');
    }

    public function it_should_contain_only_letters_numbers_and_underscores()
    {
        $this->beConstructedThrough('fromString', ['badId!']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_cannot_be_constructed_with_an_empty_string()
    {
        $this->shouldThrow('\InvalidArgumentException')->during('fromString', ['']);
    }

    public function it_cannot_be_constructed_with_a_string_too_long()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromString', [str_repeat('a', 256)]);
    }

    public function it_is_possible_to_compare_it()
    {
        $this->equals(RecordCode::fromString('an_identifier'))->shouldReturn(true);
        $this->equals(RecordCode::fromString('other_identifier'))->shouldReturn(false);
    }
}
