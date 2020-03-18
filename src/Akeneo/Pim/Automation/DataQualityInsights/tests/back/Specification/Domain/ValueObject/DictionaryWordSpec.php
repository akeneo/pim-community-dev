<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

use PhpSpec\ObjectBehavior;

final class DictionaryWordSpec extends ObjectBehavior
{
    public function it_constructs_with_alpha_characters()
    {
        $this->beConstructedWith('Dior');
        $this->__toString()->shouldReturn('Dior');
    }

    public function it_constructs_with_any_kind_of_letter_from_any_language()
    {
        $this->beConstructedWith('nätvøerkstäckningsæområde');
        $this->__toString()->shouldReturn('nätvøerkstäckningsæområde');
    }

    public function it_throws_exception_if_not_valid()
    {
        $this->beConstructedWith('');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();

        $this->beConstructedWith(' ');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();

        $this->beConstructedWith('d1or');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();

        $this->beConstructedWith('1234');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();

        $this->beConstructedWith('!?*$#');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
