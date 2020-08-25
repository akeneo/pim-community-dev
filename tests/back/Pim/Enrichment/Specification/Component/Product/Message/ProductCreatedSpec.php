<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Message;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Platform\Component\EventQueue\BusinessEvent;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductCreatedSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            'author',
            ['data'],
            1598968800,
            '523e4557-e89b-12d3-a456-426614174000'
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ProductCreated::class);
    }

    public function it_is_a_business_event(): void
    {
        $this->shouldBeAnInstanceOf(BusinessEvent::class);
    }

    public function it_returns_the_name(): void
    {
        $this->name()->shouldReturn('product.created');
    }

    public function it_returns_the_author(): void
    {
        $this->author()->shouldReturn('author');
    }

    public function it_returns_the_data(): void
    {
        $this->data()->shouldReturn(['data']);
    }

    public function it_returns_the_timestamp(): void
    {
        $this->timestamp()->shouldReturn(1598968800);
    }

    public function it_returns_the_uuid(): void
    {
        $this->uuid()->shouldReturn('523e4557-e89b-12d3-a456-426614174000');
    }
}
