<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Event;

use Akeneo\Pim\Enrichment\Component\Error\Event\DomainErrorEvent;
use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Event\ProductDomainErrorEvent;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use PhpSpec\ObjectBehavior;

class ProductDomainErrorEventSpec extends ObjectBehavior
{
    public function let(DomainErrorInterface $error, ProductInterface $product): void
    {
        $this->beConstructedWith($error, $product);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ProductDomainErrorEvent::class);
    }

    public function it_is_a_domain_error_event(): void
    {
        $this->shouldHaveType(DomainErrorEvent::class);
    }

    public function it_returns_the_product($product): void
    {
        $this->getProduct()->shouldReturn($product);
    }
}
