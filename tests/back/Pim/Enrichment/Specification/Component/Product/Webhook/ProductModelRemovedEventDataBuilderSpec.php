<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\ProductModelRemovedEventDataBuilder;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use PhpSpec\ObjectBehavior;

class ProductModelRemovedEventDataBuilderSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith();
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(ProductModelRemovedEventDataBuilder::class);
        $this->shouldImplement(EventDataBuilderInterface::class);
    }

    public function it_supports_product_model_removed_event(): void
    {
        $this->supports(new ProductModelRemoved('julia', 'ui', ['data']))->shouldReturn(true);
    }

    public function it_does_not_supports_other_business_event(): void
    {
        $this->supports(new ProductCreated('julia', 'ui', ['data']))->shouldReturn(false);
    }

    public function it_builds_product_model_removed_event(): void
    {
        $this->build(new ProductModelRemoved('julia', 'ui', ['code' => 'product_identifier']))->shouldReturn(
            [
                'resource' => ['code' => 'product_identifier'],
            ]
        );
    }

    public function it_does_not_build_other_business_event(): void
    {
        $this->shouldThrow(new \InvalidArgumentException())->during(
            'build',
            [new ProductCreated('julia', 'ui', ['code' => 'product_identifier'])]
        );
    }
}
