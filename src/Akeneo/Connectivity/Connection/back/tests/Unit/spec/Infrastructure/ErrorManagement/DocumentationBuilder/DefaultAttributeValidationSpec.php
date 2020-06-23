<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\DocumentationBuilder;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation\DocumentationCollection;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\DocumentationBuilder\DefaultAttributeValidation;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsNumeric;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Range;
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
            IsNumeric::IS_NUMERIC,
            Range::INVALID_CHARACTERS_ERROR,
            Range::TOO_HIGH_ERROR,
            Range::TOO_LOW_ERROR,
            UniqueValue::UNIQUE_VALUE
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
