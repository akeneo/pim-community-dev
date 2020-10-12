<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\ProductRemovedEventDataBuilder;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use PhpSpec\ObjectBehavior;

class ProductRemovedEventDataBuilderSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith();
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(ProductRemovedEventDataBuilder::class);
        $this->shouldImplement(EventDataBuilderInterface::class);
    }

    public function it_supports_product_removed_event(): void
    {
        $this->supports(new ProductRemoved('julia', 'ui', ['data']))->shouldReturn(true);
    }

    public function it_does_not_supports_other_business_event(): void
    {
        $this->supports(new ProductCreated('julia', 'ui', ['data']))->shouldReturn(false);
    }

    public function it_builds_product_removed_event(): void
    {
        $this->build(new ProductRemoved('julia', 'ui', ['identifier' => 'product_identifier']))->shouldReturn(
            [
                'resource' => ['identifier' => 'product_identifier'],
            ]
        );
    }

    public function it_does_not_build_other_business_event(): void
    {
        $this->shouldThrow(new \InvalidArgumentException())->during(
            'build',
            [new ProductCreated('julia', 'ui', ['identifier' => 'product_identifier'])]
        );
    }
}
