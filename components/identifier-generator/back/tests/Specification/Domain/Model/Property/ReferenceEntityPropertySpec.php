<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ReferenceEntity;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\SimpleSelect;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\ReferenceEntityProperty;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceEntityPropertySpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedThrough('fromNormalized', [
            [
                'type' => 'reference_entity',
                'attributeCode' => 'brand',
                'process' => ['type' => 'truncate', 'operator' => '=', 'value' => 3],
                'scope' => 'ecommerce',
                'locale' => 'en_US',
            ]
        ]);
    }

    public function it_is_a_reference_entity_property(): void
    {
        $this->shouldBeAnInstanceOf(ReferenceEntityProperty::class);
    }

    public function it_returns_a_type(): void
    {
        $this->type()->shouldReturn('reference_entity');
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
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
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
                'type' => 'reference_entity',
                'attributeCode' => 'brand',
                'process' => ['type' => 'truncate', 'operator' => '=', 'value' => 3],
                'scope' => 'ecommerce',
                'locale' => 'en_US'
            ]
        ]);

        $this->normalize()->shouldReturn([
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
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
            ReferenceEntity::fromNormalized([
                'type' => 'reference_entity',
                'attributeCode' => 'brand',
                'operator' => 'NOT EMPTY',
                'scope' => 'ecommerce',
                'locale' => 'en_US',
            ]),
        );
    }
}
