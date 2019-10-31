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
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\AttributesMapping;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributesMappingSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith([]);
    }

    public function it_is_an_attributes_mapping(): void
    {
        $this->shouldHaveType(AttributesMapping::class);
    }

    public function it_is_traversable(): void
    {
        $this->shouldHaveType(\Traversable::class);

        $this->getIterator()->shouldReturnAnInstanceOf(\Iterator::class);
    }

    public function it_can_be_constructed_with_attribute_mapping(): void
    {
        $this->beConstructedWith([
            new AttributeMapping([
                'from' => [
                    'id' => 1,
                    'type' => 'metric'
                ],
                'to' => [
                    'id' => 2
                ],
                'status' => AttributeMapping::STATUS_ACTIVE
            ])
        ]);


        $this->getIterator()->shouldHaveCount(1);
    }

    public function it_adds_attribute_mapping_to_the_collection(): void
    {
        $this->beConstructedWith([
            new AttributeMapping([
                'from' => [
                    'id' => 1,
                    'type' => 'metric'
                ],
                'to' => [
                    'id' => 2
                ],
                'status' => AttributeMapping::STATUS_ACTIVE
            ])
        ]);

        $this->add(new AttributeMapping([
            'from' => [
                'id' => 2,
                'type' => 'metric'
            ],
            'to' => [
                'id' => 3
            ],
            'status' => AttributeMapping::STATUS_ACTIVE
        ]));

        $this->getIterator()->shouldHaveCount(2);
    }
}
