<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DelimiterSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedThrough('fromString', ['-']);
    }

    public function it_is_a_delimiter(): void
    {
        $this->shouldBeAnInstanceOf(Delimiter::class);
    }

    public function it_returns_a_delimiter(): void
    {
        $this->asString()->shouldReturn('-');
    }

    public function it_cannot_be_instantiated_with_an_empty_string(): void
    {
        $this->beConstructedThrough('fromString', ['']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_a_delimiter_with_null_value(): void
    {
        $this->beConstructedThrough('fromString', [null]);
        $this->asString()->shouldReturn(null);
    }

    public function it_cannot_be_instantiated_with_value_too_long(): void
    {
        $this->beConstructedThrough('fromString', ['abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
