<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\AutoNumber;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AutoNumberSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromValues', [5,2]);
    }

    public function it_is_a_auto_number()
    {
        $this->shouldBeAnInstanceOf(AutoNumber::class);
    }

    public function it_cannot_be_instantiated_with_number_min_negative()
    {
        $this->beConstructedThrough('fromValues', [-5,2]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cannot_be_instantiated_with_digits_min_negative()
    {
        $this->beConstructedThrough('fromValues', [5,-2]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_a_number_min()
    {
        $this->numberMin()->shouldReturn(5);
    }

    public function it_returns_a_digits_min()
    {
        $this->digitsMin()->shouldReturn(2);
    }

    function it_normalize_an_auto_number()
    {
        $this->normalize()->shouldReturn([
            'type' => 'auto_number',
            'numberMin' => 5,
            'digitsMin' => 2,
        ]);
    }

    function it_creates_from_normalized()
    {
        $this->fromNormalized([
            'type' => 'auto_number',
            'numberMin' => 7,
            'digitsMin' => 8,
        ])->shouldBeLike(AutoNumber::fromValues(7, 8));
    }

    function it_throws_an_exception_when_type_is_bad()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'bad',
            'numberMin' => 7,
            'digitsMin' => 8,
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_an_exception_when_type_key_is_missing()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'numberMin' => 7,
            'digitsMin' => 8,
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_an_exception_when_number_min_key_is_missing()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'auto_number',
            'digitsMin' => 8,
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_an_exception_when_digits_min_key_is_missing()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'auto_number',
            'numberMin' => 7,
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_an_exception_from_normalized_with_number_min_negative()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'auto_number',
            'numberMin' => -7,
            'digitsMin' => 8,
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_an_exception_from_normalized_with_digits_min_negative()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'auto_number',
            'numberMin' => 7,
            'digitsMin' => -8,
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
