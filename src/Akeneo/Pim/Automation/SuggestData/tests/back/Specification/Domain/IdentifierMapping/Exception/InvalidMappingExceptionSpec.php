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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Exception;

use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Exception\InvalidMappingException;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class InvalidMappingExceptionSpec extends ObjectBehavior
{
    public function it_is_an_invalid_mapping_exception(): void
    {
        $this->beConstructedWith('className');

        $this->shouldBeAnInstanceOf(InvalidMappingException::class);
    }

    public function it_is_an_exception(): void
    {
        $this->beConstructedWith('className');

        $this->shouldBeAnInstanceOf(\Exception::class);
    }

    public function it_returns_the_name_of_the_class_the_exception_was_thrown_from(): void
    {
        $this->beConstructedWith('className');

        $this->getClassName()->shouldReturn('className');
    }

    public function it_is_thrown_if_an_attribute_is_mapped_several_times(): void
    {
        $this->beConstructedThrough('duplicateAttributeCode', ['className']);

        $this->getMessage()->shouldReturn(
            'akeneo_suggest_data.entity.identifier_mapping.constraint.duplicate_attribute_code'
        );
    }

    public function it_is_thrown_if_identifier_mapping_is_missing_or_invalid(): void
    {
        $this->beConstructedThrough('missingOrInvalidIdentifiersInMapping', [
            ['an_attribute_code', 'another_attribute_code'],
            'className',
        ]);

        $this->getMessage()->shouldReturn(
            'akeneo_suggest_data.entity.identifier_mapping.constraint.missing_or_invalid_identifiers'
        );
    }

    public function it_is_thrown_if_a_mapped_attribute_was_not_found(): void
    {
        $this->beConstructedThrough('attributeNotFound', ['foobar', 'className']);

        $this->getMessage()->shouldReturn(
            'akeneo_suggest_data.entity.identifier_mapping.constraint.attribute_not_found'
        );
    }

    public function it_is_thrown_if_it_miss_a_mandatory_attribute(): void
    {
        $this->beConstructedThrough('mandatoryAttributeMapping', ['foo', 'brand']);

        $this->getMessage()->shouldReturn(
            'akeneo_suggest_data.entity.identifier_mapping.constraint.mandatory_identifier_mapping'
        );
        $this->getPath()->shouldReturn('brand');
    }

    public function it_is_thrown_in_case_of_wrong_attribute_type(): void
    {
        $this->beConstructedThrough('attributeType', ['foo', 'brand']);

        $this->getMessage()->shouldReturn(
            'akeneo_suggest_data.entity.identifier_mapping.constraint.attribute_type'
        );
        $this->getPath()->shouldReturn('brand');
    }
}
