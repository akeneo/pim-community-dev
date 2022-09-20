<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\AutoNumber;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AutoNumberSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromValues', [5,2]);
    }

    function it_is_a_auto_number()
    {
        $this->shouldBeAnInstanceOf(AutoNumber::class);
    }

    function it_cannot_be_instantiated_with_minimal_number_negative()
    {
        $this->beConstructedThrough('fromValues', [-5,2]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_instantiated_with_min_digits_negative()
    {
        $this->beConstructedThrough('fromValues', [5,-2]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_represents_a_minmal_number()
    {
        $this->getMinimalNumber()->shouldReturn(5);
    }

    function it_represents_a_min_digit()
    {
        $this->getMinDigits()->shouldReturn(2);
    }
}
