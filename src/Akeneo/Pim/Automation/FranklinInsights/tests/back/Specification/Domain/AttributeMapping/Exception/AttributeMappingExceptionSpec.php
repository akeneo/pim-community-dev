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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Exception;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Exception\AttributeMappingException;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class AttributeMappingExceptionSpec extends ObjectBehavior
{
    public function it_is_an_attribute_mapping_exception(): void
    {
        $this->beConstructedWith('foo');
        $this->shouldBeAnInstanceOf(AttributeMappingException::class);
    }

    public function it_is_an_exception(): void
    {
        $this->beConstructedWith('foo');
        $this->shouldBeAnInstanceOf(\Exception::class);
    }

    public function it_returns_message_parameters(): void
    {
        $this->beConstructedWith('foo', ['bar' => 'baz']);

        $this->getMessageParams()->shouldReturn(['bar' => 'baz']);
    }

    public function it_throws_an_incompatible_attribute_type_mapping_message(): void
    {
        $pimType = AttributeTypes::DATE;
        $this->beConstructedThrough('incompatibleAttributeTypeMapping', [$pimType]);

        $this->getMessage()->shouldReturn(
            'akeneo_franklin_insights.entity.attributes_mapping.constraint.invalid_attribute_type_mapping'
        );
        $this->getMessageParams()->shouldReturn(['pimType' => $pimType]);
    }

    public function it_throws_an_exception_attribute_is_not_in_family(): void
    {
        $this->beConstructedThrough('attributeNotInFamilyNotAllowed', []);

        $this->getMessage()->shouldReturn(
            'akeneo_franklin_insights.entity.attributes_mapping.constraint.attribute_not_in_family_not_allowed'
        );
    }
}
