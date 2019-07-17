<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\ValueObject;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\ValueObject\AttributesToCreate;
use PhpSpec\ObjectBehavior;

class AttributesToCreateSpec extends ObjectBehavior
{
    private $attributesToCreate;

    public function let()
    {
        $this->attributesToCreate = [
            [
                'franklinAttributeLabel' => 'color',
                'franklinAttributeType' => 'text',
            ],
            [
                'franklinAttributeLabel' => 'height',
                'franklinAttributeType' => 'number',
            ],
        ];

        $this->beConstructedWith($this->attributesToCreate);
    }

    public function it_is_a_franklin_attributes_to_create_vo(): void
    {
        $this->shouldHaveType(AttributesToCreate::class);
    }

    public function it_is_iterable(): void
    {
        $this->shouldImplement(\IteratorAggregate::class);
    }

    public function it_throws_an_exception_when_franklin_attribute_label_key_is_missing()
    {
        $this->beConstructedWith(
            [
                [
                    'test' => 'color',
                    'franklinAttributeType' => 'text',
                ]
            ]
        );

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_when_franklin_attribute_type_key_is_missing()
    {
        $this->beConstructedWith(
            [
                [
                    'franklinAttributeLabel' => 'color',
                    'test' => 'text',
                ]
            ]
        );

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
