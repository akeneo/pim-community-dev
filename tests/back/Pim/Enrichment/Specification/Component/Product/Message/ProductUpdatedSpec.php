<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Message;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Platform\Component\EventQueue\BusinessEvent;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductUpdatedSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            'author',
            'api',
            ['data'],
            1598968800,
            '123e4567-e89b-12d3-a456-426614174000'
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ProductUpdated::class);
    }

    public function it_is_a_business_event(): void
    {
        $this->shouldBeAnInstanceOf(BusinessEvent::class);
    }

    public function it_returns_the_name(): void
    {
        $this->name()->shouldReturn('product.updated');
    }

    public function it_returns_the_author(): void
    {
        $this->author()->shouldReturn('author');
    }

    public function it_returns_the_author_type(): void
    {
        $this->authorType()->shouldReturn('api');
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
        $this->uuid()->shouldReturn('123e4567-e89b-12d3-a456-426614174000');
    }
}
