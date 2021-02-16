<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder;

use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder\AttributeOptionValidation;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\AttributeOptionsExist;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\DuplicateOptions;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeOptionValidationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beAnInstanceOf(AttributeOptionValidation::class);
    }

    function it_is_a_documentation_builder()
    {
        $this->beAnInstanceOf(DocumentationBuilderInterface::class);
    }

    function it_supports_the_error_attribute_option_does_not_exist(ConstraintViolationInterface $constraintViolation)
    {
        $constraintViolation->getCode()->willReturn(AttributeOptionsExist::ATTRIBUTE_OPTION_DOES_NOT_EXIST);

        $this->support($constraintViolation)->shouldReturn(true);
    }

    function it_supports_the_error_attribute_options_do_not_exist(ConstraintViolationInterface $constraintViolation)
    {
        $constraintViolation->getCode()->willReturn(AttributeOptionsExist::ATTRIBUTE_OPTIONS_DO_NOT_EXIST);

        $this->support($constraintViolation)->shouldReturn(true);
    }

    function it_supports_the_duplicate_attribute_options_error(ConstraintViolationInterface $constraintViolation)
    {
        $constraintViolation->getCode()->willReturn(DuplicateOptions::DUPLICATE_ATTRIBUTE_OPTIONS);

        $this->support($constraintViolation)->shouldReturn(true);
    }

    function it_does_not_support_other_types_of_error(ConstraintViolationInterface $constraintViolation)
    {
        $constraintViolation->getCode()->willReturn('other_error_code');

        $this->support($constraintViolation)->shouldReturn(false);
    }

    function it_builds_the_documentation(ConstraintViolationInterface $constraintViolation)
    {
        $constraintViolation->getCode()->willReturn(AttributeOptionsExist::ATTRIBUTE_OPTION_DOES_NOT_EXIST);
        $constraintViolation->getParameters()->willReturn([
            '%attribute_code%' => 'color'
        ]);

        $documentation = $this->buildDocumentation($constraintViolation);

        $documentation->shouldHaveType(DocumentationCollection::class);
        $documentation->normalize()->shouldReturn([
            [
                'message' => 'More information about select attributes: {manage_attributes_options}.',
                'parameters' => [
                    'manage_attributes_options' => [
                        'type' => 'href',
                        'href' => 'https://help.akeneo.com/pim/serenity/articles/manage-your-attributes.html#manage-simple-and-multi-selects-attribute-options',
                        'title' => 'Manage select attributes options',
                    ],
                ],
                'style' => 'information'
            ],
            [
                'message' => 'Please check the {attribute_options_settings}.',
                'parameters' => [
                    'attribute_options_settings' => [
                        'type' => 'route',
                        'route' => 'pim_enrich_attribute_edit',
                        'routeParameters' => [
                            'code' => 'color',
                        ],
                        'title' => 'Options settings of the color attribute',
                    ],
                ],
                'style' => 'text'
            ],
        ]);
    }

    function it_does_not_build_the_documentation_for_an_unsupported_error(
        ConstraintViolationInterface $constraintViolation
    ) {
        $constraintViolation->getCode()->willReturn('other_error_code');

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('buildDocumentation', [$constraintViolation]);
    }

    function it_does_not_build_the_documentation_if_the_attribute_code_is_not_defined_in_the_error(
        ConstraintViolationInterface $constraintViolation
    ) {
        $constraintViolation->getCode()->willReturn(AttributeOptionsExist::ATTRIBUTE_OPTION_DOES_NOT_EXIST);
        $constraintViolation->getParameters()->willReturn([]);

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('buildDocumentation', [$constraintViolation]);
    }
}
