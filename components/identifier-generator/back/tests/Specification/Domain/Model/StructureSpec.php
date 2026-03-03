<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Family;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\AutoNumber;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FamilyProperty;
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
    public function let(): void
    {
        $freeText = FreeText::fromString('ABC');
        $autoNumber = AutoNumber::fromValues(5, 2);
        $family = FamilyProperty::fromNormalized(['type' => 'family', 'process' => ['type' => 'no']]);
        $this->beConstructedThrough('fromArray', [[$freeText, $autoNumber, $family]]);
    }

    public function it_is_a_structure(): void
    {
        $this->shouldBeAnInstanceOf(Structure::class);
    }

    public function it_throws_an_exception_when_an_empty_array(): void
    {
        $this->beConstructedThrough('fromArray', [[]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_when_an_array_value_is_not_an_property(): void
    {
        $this->beConstructedThrough('fromArray', [[5, '']]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_has_properties_values(): void
    {
        $properties = $this->getProperties();
        $properties->shouldHaveCount(3);
        $properties[0]->shouldBeAnInstanceOf(PropertyInterface::class);
        $properties[1]->shouldBeAnInstanceOf(PropertyInterface::class);
        $properties[2]->shouldBeAnInstanceOf(PropertyInterface::class);
    }

    public function it_normalize_a_structure(): void
    {
        $this->normalize()->shouldReturn([
            [
                'type' => 'free_text',
                'string' => 'ABC',
            ], [
                'type' => 'auto_number',
                'numberMin' => 5,
                'digitsMin' => 2,
            ], [
                'type' => 'family',
                'process' => [
                    'type' => 'no',
                ],
            ],
        ]);
    }

    public function it_creates_from_normalized(): void
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
            AutoNumber::fromValues(5, 6),
        ]));
    }

    public function it_should_get_implicit_conditions(): void
    {
        $this->getImplicitConditions()->shouldBeLike([
            Family::fromNormalized(['type' => 'family', 'operator' => 'NOT EMPTY']),
        ]);
    }
}
