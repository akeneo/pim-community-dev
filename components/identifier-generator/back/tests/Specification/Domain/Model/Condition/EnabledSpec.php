<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Enabled;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EnabledSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedThrough('fromBoolean', [true]);
    }

    public function it_is_a_enabled(): void
    {
        $this->shouldBeAnInstanceOf(Enabled::class);
    }

    public function it_normalize_an_enabled(): void
    {
        $this->normalize()->shouldReturn([
            'type' => 'enabled',
            'value' => true,
        ]);
    }

    public function it_creates_from_normalized(): void
    {
        $this->fromNormalized([
            'type' => 'enabled',
            'value' => false,
        ])->shouldBeLike(Enabled::fromBoolean(false));
    }

    public function it_throws_an_exception_when_type_is_bad(): void
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'bad',
            'value' => true,
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_when_type_key_is_missing(): void
    {
        $this->beConstructedThrough('fromNormalized', [[
            'value' => 'ABC',
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_from_normalized_with_string(): void
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'enabled',
            'value' => 'abc',
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
