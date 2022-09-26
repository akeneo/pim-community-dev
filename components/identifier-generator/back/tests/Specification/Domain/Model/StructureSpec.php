<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\AutoNumber;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\PropertyInterface;
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
        $properties->shouldHaveCount(2);
        $properties[0]->shouldBeAnInstanceOf(PropertyInterface::class);
        $properties[1]->shouldBeAnInstanceOf(PropertyInterface::class);
    }

    function it_normalize_a_structure()
    {
        $this->normalize()->shouldReturn([
            [
                'type' => 'free_text',
                'string' => 'ABC',
            ],
            [
                'type' => 'auto_number',
                'numberMin' => 5,
                'digitsMin' => 2,
            ],
        ]);
    }

    function it_creates_from_normalized()
    {
        $this->fromNormalized([
            [
                'type' => 'free_text',
                'string' => 'CBA',
            ],
            [
                'type' => 'auto_number',
                'numberMin' => 5,
                'digitsMin' => 6,
            ],
        ])->shouldBeLike(Structure::fromArray([
            FreeText::fromString('CBA'),
            AutoNumber::fromValues(5,6),
        ]));
    }
}
