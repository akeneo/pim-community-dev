<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\AutoNumber;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StructureSpec extends ObjectBehavior
{
    function let()
    {
        $freeText = FreeText::fromString('ABC');
        $autoNumber = AutoNumber::fromValues(5,2);
        $this->beConstructedThrough('fromArray', [[$freeText, $autoNumber]]);
    }

    function it_is_a_structure()
    {
        $this->shouldBeAnInstanceOf(Structure::class);
    }

    function it_throws_an_exception_when_en_empty_array()
    {
        $this->beConstructedThrough('fromArray', [[]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_an_exception_when_an_array_value_is_not_an_property()
    {
        $this->beConstructedThrough('fromArray', [[5, '']]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_has_properties_values()
    {
        $properties = $this->getProperties();
        $properties[0]->asString()->shouldReturn('ABC');

        $properties[1]->getMinimalNumber()->shouldReturn(5);
        $properties[1]->getMinDigits()->shouldReturn(2);
    }
}
