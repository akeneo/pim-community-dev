<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ConditionInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Family;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilySpec extends ObjectBehavior
{
    public function let(): void
    {
    }

    public function it_is_a_family(): void
    {
        $this->shouldImplement(ConditionInterface::class);
        $this->shouldBeAnInstanceOf(Family::class);
    }

    public function it_should_throw_exception_if_type_is_not_family()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'bad',
            'operator' => 'EMPTY',
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_throw_exception_if_no_operator_is_defined()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'family',
            'value' => ['shirts'],
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_throw_exception_if_operator_is_not_a_string()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'family',
            'operator' => true,
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_throw_exception_if_value_is_not_defined()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'family',
            'operator' => 'IN',
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_throw_exception_if_value_is_not_an_array_of_strings()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'family',
            'operator' => 'IN',
            'value' => [true],
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_throw_exception_if_value_is_empty()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'family',
            'operator' => 'IN',
            'value' => [],
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_throw_exception_if_value_defined()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'family',
            'operator' => 'EMPTY',
            'value' => ['shirts'],
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_normalize_without_value()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'family',
            'operator' => 'EMPTY'
        ]]);
        $this->normalize()->shouldReturn([
            'type' => 'family',
            'operator' => 'EMPTY',
        ]);
    }

    public function it_should_normalize_with_value()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'family',
            'operator' => 'IN',
            'value' => ['shirts']
        ]]);
        $this->normalize()->shouldReturn([
            'type' => 'family',
            'operator' => 'IN',
            'value' => ['shirts'],
        ]);
    }

    public function it_should_match_empty()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'family',
            'operator' => 'EMPTY',
        ]]);
        $this->match(new ProductProjection('identifier', true, null, []))->shouldReturn(true);
    }

    public function it_should_not_match_empty()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'family',
            'operator' => 'EMPTY',
        ]]);
        $this->match(new ProductProjection('identifier', true, 'familyCode', []))->shouldReturn(false);
    }

    public function it_should_match_not_empty()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'family',
            'operator' => 'NOT EMPTY',
        ]]);
        $this->match(new ProductProjection('identifier', true, 'familyCode', []))->shouldReturn(true);
    }

    public function it_should_not_match_not_empty()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'family',
            'operator' => 'NOT EMPTY',
        ]]);
        $this->match(new ProductProjection('identifier', true, null, []))->shouldReturn(false);
    }

    public function it_should_match_in()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'family',
            'operator' => 'IN',
            'value' => ['shirts', 'jeans'],
        ]]);
        $this->match(new ProductProjection('identifier', true, 'shirts', []))->shouldReturn(true);
    }

    public function it_should_not_match_in()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'family',
            'operator' => 'IN',
            'value' => ['shirts', 'jeans'],
        ]]);
        $this->match(new ProductProjection('identifier', true, 'jackets', []))->shouldReturn(false);
    }

    public function it_should_match_not_in()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'family',
            'operator' => 'NOT IN',
            'value' => ['shirts', 'jeans'],
        ]]);
        $this->match(new ProductProjection('identifier', true, 'jackets', []))->shouldReturn(true);
    }

    public function it_should_not_match_not_in()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'family',
            'operator' => 'NOT IN',
            'value' => ['shirts', 'jeans'],
        ]]);
        $this->match(new ProductProjection('identifier', true, 'shirts', []))->shouldReturn(false);
    }

    public function it_should_not_match_not_in_when_product_has_no_family()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'family',
            'operator' => 'NOT IN',
            'value' => ['shirts', 'jeans'],
        ]]);
        $this->match(new ProductProjection('identifier', true, null, []))->shouldReturn(false);
    }
}
