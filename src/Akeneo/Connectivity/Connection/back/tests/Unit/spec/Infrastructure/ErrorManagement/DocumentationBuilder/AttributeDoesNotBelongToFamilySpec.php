<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\DocumentationBuilder;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation\DocumentationCollection;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\DocumentationBuilder\AttributeDoesNotBelongToFamily;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\OnlyExpectedAttributes;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeDoesNotBelongToFamilySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beAnInstanceOf(AttributeDoesNotBelongToFamily::class);
    }

    function it_is_a_documentation_builder()
    {
        $this->beAnInstanceOf(DocumentationBuilderInterface::class);
    }

    function it_supports_the_error_attribute_does_not_belong_to_family(
        ConstraintViolationInterface $constraintViolation
    ) {
        $constraintViolation->getCode()->willReturn(OnlyExpectedAttributes::ATTRIBUTE_DOES_NOT_BELONG_TO_FAMILY);

        $this->support($constraintViolation)->shouldReturn(true);
    }

    function it_does_not_support_other_types_of_error(ConstraintViolationInterface $constraintViolation)
    {
        $constraintViolation->getCode()->willReturn('other_error_code');

        $this->support($constraintViolation)->shouldReturn(false);
    }

    function it_builds_the_documentation(ConstraintViolationInterface $constraintViolation)
    {
        $constraintViolation->getCode()->willReturn(OnlyExpectedAttributes::ATTRIBUTE_DOES_NOT_BELONG_TO_FAMILY);
        $constraintViolation->getParameters()->willReturn([
            '%family%' => 'shoes'
        ]);

        $documentation = $this->buildDocumentation($constraintViolation);

        $documentation->shouldHaveType(DocumentationCollection::class);
        $documentation->normalize()->shouldReturn([
            [
                'message' => 'More information about family attributes settings: {manage_family_attributes}.',
                'parameters' => [
                    'manage_family_attributes' => [
                        'type' => 'href',
                        'href' => 'https://help.akeneo.com/pim/serenity/articles/manage-your-families.html#manage-attributes-in-a-family',
                        'title' => 'Manage attributes in a family',
                    ],
                ],
                'type' => 'information',
            ],
            [
                'message' => 'Please check the {family_settings} of the shoes family.',
                'parameters' => [
                    'family_settings' => [
                        'type' => 'route',
                        'route' => 'pim_enrich_family_edit',
                        'routeParameters' => [
                            'code' => 'shoes',
                        ],
                        'title' => '"Attributes" settings',
                    ],
                ],
                'type' => 'text',
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

    function it_does_not_build_the_documentation_if_the_attribute_code_is_not_defined_in_the_error(
        ConstraintViolationInterface $constraintViolation
    ) {
        $constraintViolation->getCode()->willReturn(OnlyExpectedAttributes::ATTRIBUTE_DOES_NOT_BELONG_TO_FAMILY);
        $constraintViolation->getParameters()->willReturn([]);

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('buildDocumentation', [$constraintViolation]);
    }
}
