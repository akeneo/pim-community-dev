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
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class InvalidMappingExceptionSpec extends ObjectBehavior
{
    function it_is_an_invalid_mapping_exception()
    {
        $this->beConstructedWith('className');

        $this->shouldBeAnInstanceOf(InvalidMappingException::class);
    }

    function it_is_an_invalid_argument_exception()
    {
        $this->beConstructedWith('className');

        $this->shouldBeAnInstanceOf(\InvalidArgumentException::class);
    }

    function it_returns_the_name_of_the_class_the_exception_was_thrown_from()
    {
        $this->beConstructedWith('className');

        $this->getClassName()->shouldReturn('className');
    }

    function it_is_thrown_if_an_attribute_is_mapped_several_times()
    {
        $this->beConstructedThrough('duplicateAttributeCode', [2, 'attribute_code', 'className']);

        $this->getMessage()->shouldReturn(
            'An attribute cannot be used more than once. Attribute "attribute_code" has been used 2 times.'
        );
    }

    function it_is_thrown_if_identifier_mapping_is_missing_or_invalid()
    {
        $this->beConstructedThrough('missingOrInvalidIdentifiersInMapping', [
            ['expected_attribute_code_1', 'expected_code_attribute_2'],
            ['an_attribute_code', 'another_attribute_code'],
            'className'
        ]);

        $this->getMessage()->shouldReturn(
            'Some identifiers mapping keys are missing or invalid. Expected: "array (
  0 => \'expected_attribute_code_1\',
  1 => \'expected_code_attribute_2\',
)", got "array (
  0 => \'an_attribute_code\',
  1 => \'another_attribute_code\',
)"'
        );
    }

    function it_is_thrown_if_a_mapped_attribute_was_not_found()
    {
        $this->beConstructedThrough('attributeNotFound', ['foobar', 'className']);

        $this->getMessage()->shouldReturn(
            'Attribute with attribute code "foobar" for the identifiers mapping does not exist'
        );
    }
}
