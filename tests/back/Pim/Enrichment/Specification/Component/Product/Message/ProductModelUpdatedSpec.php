<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Message;

use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Platform\Component\EventQueue\Event;
use PhpSpec\ObjectBehavior;

class ProductModelUpdatedSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            Author::fromNameAndType('julia', Author::TYPE_UI),
            ['code' => 'product_model_code', 'origin' => 'API'],
            1598968800,
            '523e4557-e89b-12d3-a456-426614174000',
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ProductModelUpdated::class);
    }

    public function it_is_an_event(): void
    {
        $this->shouldBeAnInstanceOf(Event::class);
    }

    public function it_validates_the_product_model_code(): void
    {
        $this->beConstructedWith(
            Author::fromNameAndType('julia', Author::TYPE_UI),
            [],
            1598968800,
            '523e4557-e89b-12d3-a456-426614174000',
        );

        $this->shouldThrow(new \InvalidArgumentException('Expected the key "code" to exist.'))->duringInstantiation();
    }

    public function it_returns_the_name(): void
    {
        $this->getName()->shouldReturn('product_model.updated');
    }

    public function it_returns_the_author(): void
    {
        $this->getAuthor()->shouldBeLike(Author::fromNameAndType('julia', Author::TYPE_UI));
    }

    public function it_returns_the_data(): void
    {
        $this->getData()->shouldReturn(['code' => 'product_model_code', 'origin' => 'API']);
    }

    public function it_returns_the_timestamp(): void
    {
        $this->getTimestamp()->shouldReturn(1598968800);
    }

    public function it_returns_the_uuid(): void
    {
        $this->getUuid()->shouldReturn('523e4557-e89b-12d3-a456-426614174000');
    }

    public function it_returns_the_product_model_code(): void
    {
        $this->getCode()->shouldReturn('product_model_code');
    }

    public function it_returns_origin(): void
    {
        $this->getOrigin()->shouldReturn('API');
    }
}
