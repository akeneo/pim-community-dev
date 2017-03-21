<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\Validator\Constraints\UniqueVariantAxisValidator;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UniqueVariantAxisValidatorSpec extends ObjectBehavior
{
    function let(ProductRepositoryInterface $productRepository, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($productRepository);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UniqueVariantAxisValidator::class);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldBeAnInstanceOf(ConstraintValidator::class);
    }
}
