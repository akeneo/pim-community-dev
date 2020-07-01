<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Event;

use Akeneo\Pim\Enrichment\Bundle\Event\ProductValidationErrorEvent;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ProductValidationErrorEventSpec extends ObjectBehavior
{
    public function let(ConstraintViolationListInterface $constraintViolationList, ProductInterface $product): void
    {
        $this->beConstructedWith($constraintViolationList, $product);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ProductValidationErrorEvent::class);
    }

    public function it_returns_the_constraint_violation_list($constraintViolationList): void
    {
        $this->getConstraintViolationList()->shouldReturn($constraintViolationList);
    }

    public function it_returns_the_product($product): void
    {
        $this->getProduct()->shouldReturn($product);
    }
}
