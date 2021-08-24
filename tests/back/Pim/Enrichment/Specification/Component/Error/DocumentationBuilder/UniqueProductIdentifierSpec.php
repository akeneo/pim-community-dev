<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder;

use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder\UniqueProductIdentifier;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product\UniqueProductEntity;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueProductIdentifierSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beAnInstanceOf(UniqueProductIdentifier::class);
    }

    function it_is_a_documentation_builder()
    {
        $this->beAnInstanceOf(DocumentationBuilderInterface::class);
    }

    function it_supports_unique_product_entity_constraint(ConstraintViolationInterface $constraintViolation)
    {
        $constraintViolation->getCode()->willReturn(UniqueProductEntity::UNIQUE_PRODUCT_ENTITY);
        $this->support($constraintViolation)->shouldReturn(true);
    }

    function it_does_not_support_a_random_constraint(ConstraintViolationInterface $constraintViolation)
    {
        $this->support($constraintViolation)->shouldReturn(false);

        $constraintViolation->getCode()->willReturn('random code');

        $this->support($constraintViolation)->shouldReturn(false);
    }

    function it_does_not_support_a_random_object(\Exception $exception)
    {
        $this->support($exception)->shouldReturn(false);
    }
    function it_builds_the_documentation(ConstraintViolationInterface $constraintViolation)
    {
        $constraintViolation->getCode()->willReturn(UniqueProductEntity::UNIQUE_PRODUCT_ENTITY);

        $documentation = $this->buildDocumentation($constraintViolation);

        $documentation->normalize()->shouldReturn([
            [
                'message' => 'More information about identifier attributes: {attribute_types}',
                'parameters' => [
                    'attribute_types' => [
                        'type' => 'href',
                        'href' => 'https://help.akeneo.com/pim/serenity/articles/what-is-an-attribute.html#akeneo-attribute-types',
                        'title' => 'Akeneo attribute types',
                    ],
                ],
                'style' => 'information'
            ]
        ]);
    }

    function it_throws_an_exception_on_documentation_build_with_unsupported_constraint(ConstraintViolationInterface $constraintViolation)
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('buildDocumentation', [$constraintViolation]);
    }
}
