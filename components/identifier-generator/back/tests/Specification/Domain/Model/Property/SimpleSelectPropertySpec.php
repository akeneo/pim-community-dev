<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\SimpleSelect;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\SimpleSelectProperty;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleSelectPropertySpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedThrough('fromNormalized', [
            [
                'type' => 'simple_select',
                'attributeCode' => 'color',
                'process' => ['type' => 'truncate', 'operator' => '=', 'value' => 3],
                'scope' => 'ecommerce',
                'locale' => 'en_US',
            ]
        ]);
    }

    public function it_is_a_simple_select_property(): void
    {
        $this->shouldBeAnInstanceOf(SimpleSelectProperty::class);
    }

    public function it_returns_a_type(): void
    {
        $this->type()->shouldReturn('simple_select');
    }

    public function it_returns_a_process(): void
    {
        $process = Process::fromNormalized(['type' => 'truncate', 'operator' => '=', 'value' => 3]);
        $this->process()->shouldBeAnInstanceOf(Process::class);
        $this->process()->shouldBeLike($process);
    }

    public function it_normalizes_itself(): void
    {
        $this->normalize()->shouldReturn([
            'type' => 'simple_select',
            'attributeCode' => 'color',
            'process' => [
                'type' => 'truncate',
                'operator' => '=',
                'value' => 3
            ],
            'scope' => 'ecommerce',
            'locale' => 'en_US',
        ]);
    }

    public function it_normalizes_itself_with_scope_and_locale(): void
    {
        $this->beConstructedThrough('fromNormalized', [
            [
                'type' => 'simple_select',
                'attributeCode' => 'color',
                'process' => ['type' => 'truncate', 'operator' => '=', 'value' => 3],
                'scope' => 'ecommerce',
                'locale' => 'en_US'
            ]
        ]);

        $this->normalize()->shouldReturn([
            'type' => 'simple_select',
            'attributeCode' => 'color',
            'process' => [
                'type' => 'truncate',
                'operator' => '=',
                'value' => 3
            ],
            'scope' => 'ecommerce',
            'locale' => 'en_US'
        ]);
    }

    public function it_should_return_an_implicit_condition(): void
    {
        $this->getImplicitCondition()->shouldBeLike(
            SimpleSelect::fromNormalized([
                'type' => 'simple_select',
                'attributeCode' => 'color',
                'operator' => 'NOT EMPTY',
                'scope' => 'ecommerce',
                'locale' => 'en_US',
            ]),
        );
    }
}
