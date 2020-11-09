<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\ProductRemovedEventDataBuilder;
use Akeneo\Platform\Component\EventQueue\Author;
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
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);

        $this->supports(new ProductRemoved($author, ['data']))->shouldReturn(true);
    }

    public function it_does_not_supports_other_business_event(): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);

        $this->supports(new ProductCreated($author, ['identifier' => '1']))->shouldReturn(false);
    }

    public function it_builds_product_removed_event(): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);

        $this->build(new ProductRemoved($author, ['identifier' => 'product_identifier']))->shouldReturn(
            [
                'resource' => ['identifier' => 'product_identifier'],
            ]
        );
    }

    public function it_does_not_build_other_business_event(): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);

        $this->shouldThrow(new \InvalidArgumentException())->during(
            'build',
            [new ProductCreated($author, ['identifier' => '1'])]
        );
    }
}
