<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\AttributeMapping;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeMappingSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith([
            'from' => [
                'id' => 'product_weight',
                'label' => [
                    'en_us' => 'Product Weight',
                ],
                'type' => 'metric',
            ],
            'to' => ['id' => 'color'],
            'summary' => ['23kg',  '12kg'],
            'status' => 'pending',
            'suggestions' => ['weight']
        ]);
    }

    public function it_is_an_attribute_mapping(): void
    {
        $this->shouldHaveType(AttributeMapping::class);
    }

    public function it_gets_target_attribute_code(): void
    {
        $this->getTargetAttributeCode()->shouldReturn('product_weight');
    }

    public function it_gets_target_attribute_label(): void
    {
        $this->getTargetAttributeLabel()->shouldReturn('Product Weight');
    }

    public function it_gets_null_pim_attribute_code_if_not_mapped_yet(): void
    {
        $this->beConstructedWith([
            'from' => [
                'id' => 'product_weight',
                'type' => 'metric',
            ],
            'to' => null,
            'status' => 'pending',
        ]);

        $this->getPimAttributeCode()->shouldReturn(null);
    }

    public function it_gets_pim_attribute_code(): void
    {
        $this->getPimAttributeCode()->shouldReturn('color');
    }

    public function it_gets_attribute_mapping_status(): void
    {
        $this->getStatus()->shouldReturn(AttributeMapping::STATUS_PENDING);
    }

    public function it_gets_summary(): void
    {
        $this->getSummary()->shouldReturn(['23kg',  '12kg']);
    }

    public function it_gives_suggestions()
    {
        $this->getSuggestions()->shouldReturn(['weight']);
    }

    public function it_throws_an_exception_if_some_fields_are_missing(): void
    {
        $this->beConstructedWith([]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_if_status_is_invalid(): void
    {
        $this->beConstructedWith([
            'from' => [
                'id' => 'product_weight',
                'type' => 'metric',
            ],
            'to' => null,
            'status' => 'invalid-status',
        ]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_if_attribute_type_is_invalid(): void
    {
        $this->beConstructedWith([
            'from' => [
                'id' => 'product_weight',
                'type' => 'unhandled-type',
            ],
            'to' => null,
            'status' => 'pending',
        ]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_if_target_attribute_code_is_malformed(): void
    {
        $this->beConstructedWith([
            'from' => [
                'type' => 'metric',
            ],
            'to' => null,
            'status' => 'pending',
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_if_pim_attribute_code_is_malformed(): void
    {
        $this->beConstructedWith([
            'from' => [
                'id' => 'product_weight',
                'type' => 'metric',
            ],
            'to' => 'invalid-value',
            'status' => 'pending',
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_if_the_suggestions_are_not_an_array()
    {
        $this->beConstructedWith([
            'from' => [
                'id' => 'product_weight',
                'type' => 'metric',

                'label' => [
                    'en_us' => 'Product Weight',
                ],
            ],
            'to' => ['id' => 'color'],
            'summary' => ['23kg',  '12kg'],
            'status' => 'pending',
            'suggestions' => 'weight'
        ]);

        $this->shouldThrow(new \InvalidArgumentException('The property "suggestions" is not an array'))->duringInstantiation();
    }

    public function it_throws_an_exception_if_the_suggestions_are_malformed()
    {
        $this->beConstructedWith([
            'from' => [
                'id' => 'product_weight',
                'type' => 'metric',
                'label' => [
                    'en_us' => 'Product Weight',
                ],
            ],
            'to' => ['id' => 'color'],
            'summary' => ['23kg',  '12kg'],
            'status' => 'pending',
            'suggestions' => [
                ['id' => 'weight']
            ]
        ]);

        $this->shouldThrow(new \InvalidArgumentException('The property "suggestions" is malformed'))->duringInstantiation();
    }
}
