<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

use PhpSpec\ObjectBehavior;

final class LanguageCodeSpec extends ObjectBehavior
{
    public function it_constructs_valid_language_code()
    {
        $this->beConstructedWith('en');

        $this->__toString()->shouldReturn('en');
    }

    public function it_throws_exception_if_not_valid()
    {
        $this->beConstructedWith('aaa');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();

        $this->beConstructedWith('11');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();

        $this->beConstructedWith('EN');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();

        $this->beConstructedWith('En');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();

        $this->beConstructedWith('eN');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();

        $this->beConstructedWith('  ');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
