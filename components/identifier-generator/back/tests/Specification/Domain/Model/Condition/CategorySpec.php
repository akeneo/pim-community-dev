<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Category;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ConditionInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategorySpec extends ObjectBehavior
{
    public function let(): void
    {
    }

    public function it_is_a_category(): void
    {
        $this->shouldImplement(ConditionInterface::class);
        $this->shouldBeAnInstanceOf(Category::class);
    }

    public function it_cant_be_instanciated_if_type_is_not_category()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'bad',
            'operator' => 'IN',
            'value' => ['tshirts'],
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cant_be_instanciated_if_no_operator_is_defined()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'category',
            'value' => ['tshirts'],
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cant_be_instanciated_if_operator_is_not_a_string()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'category',
            'operator' => true,
            'value' => ['tshirts'],
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cant_be_instanciated_if_operator_is_unknown()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'category',
            'operator' => 'EMPTY',
            'value' => ['tshirts'],
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cant_be_instanciated_if_value_is_not_defined()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'category',
            'operator' => 'IN',
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cant_be_instanciated_if_value_is_not_an_array_of_strings()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'category',
            'operator' => 'IN',
            'value' => [true],
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cant_be_instanciated_if_value_is_empty()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'category',
            'operator' => 'IN',
            'value' => [],
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_can_be_normalized_with_value_and_in_operator()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'category',
            'operator' => 'IN',
            'value' => ['pants', 'shoes'],
        ]]);
        $this->normalize()->shouldReturn([
            'type' => 'category',
            'operator' => 'IN',
            'value' => ['pants', 'shoes']
        ]);
    }

    public function it_can_be_normalized_without_value_and_classified_operator()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'category',
            'operator' => 'CLASSIFIED',
        ]]);
        $this->normalize()->shouldReturn([
            'type' => 'category',
            'operator' => 'CLASSIFIED',
        ]);
    }
}
