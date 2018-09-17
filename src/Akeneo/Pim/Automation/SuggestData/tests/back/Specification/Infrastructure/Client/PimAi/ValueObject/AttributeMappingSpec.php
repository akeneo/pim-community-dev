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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\AttributeMapping;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeMappingSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith([
            'from' => [
                'id' => 'product_weight',
                'label' => [
                    'en_us' => 'Product Weight',
                ]
            ],
            'to' => ['id' => 'color'],
            'type' => 'metric',
            'summary' => ['23kg',  '12kg'],
            'status' => 'pending',
        ]);
    }

    public function it_is_an_attribute_mapping()
    {
        $this->shouldHaveType(AttributeMapping::class);
    }

    public function it_gets_target_attribute_code()
    {
        $this->getTargetAttributeCode()->shouldReturn('product_weight');
    }

    public function it_gets_target_attribute_label()
    {
        $this->getTargetAttributeLabel()->shouldReturn('Product Weight');
    }

    public function it_gets_null_pim_attribute_code_if_not_mapped_yet()
    {
        $this->beConstructedWith([
            'from' => [
                'id' => 'product_weight',
            ],
            'to' => null,
            'type' => 'metric',
            'status' => 'pending',
        ]);

        $this->getPimAttributeCode()->shouldReturn(null);
    }

    public function it_gets_pim_attribute_code()
    {
        $this->getPimAttributeCode()->shouldReturn('color');
    }

    public function it_gets_attribute_mapping_status()
    {
        $this->getStatus()->shouldReturn(AttributeMapping::STATUS_PENDING);
    }

    public function it_gets_summary()
    {
        $this->getSummary()->shouldReturn(['23kg',  '12kg']);
    }

    public function it_throws_an_exception_if_some_fields_are_missing()
    {
        $this->beConstructedWith([]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_if_status_is_invalid()
    {
        $this->beConstructedWith([
            'from' => [
                'id' => 'product_weight',
            ],
            'to' => null,
            'type' => 'metric',
            'status' => 'invalid-status',
        ]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_if_target_attribute_code_is_malformed()
    {
        $this->beConstructedWith([
            'from' => [],
            'to' => null,
            'type' => 'metric',
            'status' => 'pending',
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_if_pim_attribute_code_is_malformed()
    {
        $this->beConstructedWith([
            'from' => [
                'id' => 'product_weight',
            ],
            'to' => 'invalid-value',
            'type' => 'metric',
            'status' => 'pending',
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
