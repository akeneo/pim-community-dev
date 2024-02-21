<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder;

use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder\NotEmptyFamily;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotEmptyFamily as ConstraintNotEmptyFamily;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotEmptyFamilySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beAnInstanceOf(NotEmptyFamily::class);
    }

    function it_is_a_documentation_builder()
    {
        $this->beAnInstanceOf(DocumentationBuilderInterface::class);
    }

    function it_supports_not_empty_family_constraint(ConstraintViolationInterface $constraintViolation)
    {
        $constraintViolation->getCode()->willReturn(ConstraintNotEmptyFamily::NOT_EMPTY_FAMILY);
        $this->support($constraintViolation)->shouldReturn(true);
    }

    function it_does_not_support_a_random_constraint(ConstraintViolationInterface $constraintViolation)
    {
        $constraintViolation->getCode()->willReturn('a_code');
        $this->support($constraintViolation)->shouldReturn(false);
    }

    function it_does_not_support_a_random_exception(\Exception $exception)
    {
        $this->support($exception)->shouldReturn(false);
    }

    function it_builds_the_documentation(ConstraintViolationInterface $constraintViolation)
    {
        $constraintViolation->getCode()->willReturn(ConstraintNotEmptyFamily::NOT_EMPTY_FAMILY);

        $documentation = $this->buildDocumentation($constraintViolation);

        $documentation->normalize()->shouldReturn([
            [
                'message' => 'More information about variant products: {product_variant}',
                'parameters' => [
                    'product_variant' => [
                        'type' => 'href',
                        'href' => 'https://help.akeneo.com/pim/serenity/articles/what-about-products-variants.html',
                        'title' => 'What about products with variants?',
                    ],
                ],
                'style' => 'information'
            ]
        ]);
    }

    function it_does_not_build_the_documentation_for_an_unsupported_constraint(ConstraintViolationInterface $constraintViolation)
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('buildDocumentation', [$constraintViolation]);
    }
}
