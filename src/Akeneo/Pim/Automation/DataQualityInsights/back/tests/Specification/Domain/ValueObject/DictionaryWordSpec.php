<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

use PhpSpec\ObjectBehavior;

final class DictionaryWordSpec extends ObjectBehavior
{
    public function it_constructs_valid_language_code()
    {
        $this->beConstructedWith('Dior');

        $this->__toString()->shouldReturn('Dior');
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
