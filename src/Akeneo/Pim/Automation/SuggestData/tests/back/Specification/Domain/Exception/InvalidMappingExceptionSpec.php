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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Domain\Exception;

use Akeneo\Pim\Automation\SuggestData\Domain\Exception\InvalidMappingException;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\SuggestDataException;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class InvalidMappingExceptionSpec extends ObjectBehavior
{
    public function it_is_an_invalid_mapping_exception()
    {
        $this->beConstructedWith('className');

        $this->shouldBeAnInstanceOf(InvalidMappingException::class);
    }

    public function it_is_an_exception()
    {
        $this->beConstructedWith('className');

        $this->shouldBeAnInstanceOf(\Exception::class);
    }

    public function it_returns_the_name_of_the_class_the_exception_was_thrown_from()
    {
        $this->beConstructedWith('className');

        $this->getClassName()->shouldReturn('className');
    }

    public function it_is_thrown_if_an_attribute_is_mapped_several_times()
    {
        $this->beConstructedThrough('duplicateAttributeCode', [2, 'attribute_code', 'className']);

        $this->getMessage()->shouldReturn(
            'akeneo_suggest_data.entity.identifier_mapping.constraint.duplicate_attribute_code'
        );
    }

    public function it_is_thrown_if_identifier_mapping_is_missing_or_invalid()
    {
        $this->beConstructedThrough('missingOrInvalidIdentifiersInMapping', [
            ['expected_attribute_code_1', 'expected_code_attribute_2'],
            ['an_attribute_code', 'another_attribute_code'],
            'className'
        ]);

        $this->getMessage()->shouldReturn(
            'akeneo_suggest_data.entity.identifier_mapping.constraint.missing_or_invalid_identifiers'
        );
    }

    public function it_is_thrown_if_a_mapped_attribute_was_not_found()
    {
        $this->beConstructedThrough('attributeNotFound', ['foobar', 'className']);

        $this->getMessage()->shouldReturn(
            'akeneo_suggest_data.entity.identifier_mapping.constraint.attribute_not_found'
        );
    }

    function it_is_thrown_if_it_miss_a_mandatory_attribute()
    {
        $this->beConstructedThrough('mandatoryAttributeMapping', ['foo', 'brand']);

        $this->getMessage()->shouldReturn(
            'akeneo_suggest_data.entity.identifier_mapping.constraint.mandatory_attribute_mapping'
        );
        $this->getPath()->shouldReturn('brand');
    }

    function it_is_thrown_in_case_of_wrong_attribute_type()
    {
        $this->beConstructedThrough('attributeType', ['foo', 'brand']);

        $this->getMessage()->shouldReturn(
            'akeneo_suggest_data.entity.identifier_mapping.constraint.attribute_type'
        );
        $this->getPath()->shouldReturn('brand');
    }
}
