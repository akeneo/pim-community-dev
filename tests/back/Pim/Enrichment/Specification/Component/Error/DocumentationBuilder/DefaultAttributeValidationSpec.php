<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder;

use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder\DefaultAttributeValidation;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Boolean;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsNumeric;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsString;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Length;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotBlank;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotDecimal;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Range;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Regex;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\UniqueValue;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DefaultAttributeValidationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beAnInstanceOf(DefaultAttributeValidation::class);
    }

    function it_is_a_documentation_builder()
    {
        $this->beAnInstanceOf(DocumentationBuilderInterface::class);
    }

    function it_supports_some_attribute_validation_constraints(ConstraintViolationInterface $constraintViolation)
    {
        $constraintCodes = [
            Boolean::NOT_BOOLEAN_ERROR,
            IsNumeric::IS_NUMERIC,
            IsString::IS_STRING,
            Length::TOO_LONG_ERROR,
            NotBlank::IS_BLANK_ERROR,
            NotDecimal::NOT_DECIMAL,
            Range::INVALID_CHARACTERS_ERROR,
            Range::NOT_IN_RANGE_ERROR,
            Range::TOO_HIGH_ERROR,
            Range::TOO_LOW_ERROR,
            Regex::REGEX_FAILED_ERROR,
            UniqueValue::UNIQUE_VALUE,
        ];

        foreach ($constraintCodes as $contraintCode) {
            $constraintViolation->getCode()->willReturn($contraintCode);
            $this->support($constraintViolation)->shouldReturn(true);
        }
    }

    function it_does_not_support_other_types_of_error(ConstraintViolationInterface $constraintViolation)
    {
        $constraintViolation->getCode()->willReturn('other_error_code');

        $this->support($constraintViolation)->shouldReturn(false);
    }

    function it_builds_the_documentation(ConstraintViolationInterface $constraintViolation)
    {
        $constraintViolation->getCode()->willReturn(UniqueValue::UNIQUE_VALUE);

        $documentation = $this->buildDocumentation($constraintViolation);

        $documentation->shouldHaveType(DocumentationCollection::class);
        $documentation->normalize()->shouldReturn([
            [
                'message' => 'More information about attributes and their validation: {manage_attributes} {manage_validation_parameters}',
                'parameters' => [
                    'manage_attributes' => [
                        'type' => 'href',
                        'href' => 'https://help.akeneo.com/pim/serenity/articles/manage-your-attributes.html',
                        'title' => 'Manage your attributes',
                    ],
                    'manage_validation_parameters' => [
                        'type' => 'href',
                        'href' => 'https://help.akeneo.com/pim/serenity/articles/manage-your-attributes.html#add-attributes-validation-parameters',
                        'title' => 'Manage your validation parameters',
                    ],
                ],
                'style' => 'information',
            ],
            [
                'message' => 'Please check your {attribute_settings}.',
                'parameters' => [
                    'attribute_settings' => [
                        'type' => 'route',
                        'route' => 'pim_enrich_attribute_index',
                        'routeParameters' => [],
                        'title' => 'Attributes settings',
                    ],
                ],
                'style' => 'text',
            ]
        ]);
    }

    function it_does_not_build_the_documentation_for_an_unsupported_error(
        ConstraintViolationInterface $constraintViolation
    ) {
        $constraintViolation->getCode()->willReturn('other_error_code');

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('buildDocumentation', [$constraintViolation]);
    }
}
