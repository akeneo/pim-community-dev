<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Webhook\EventNormalizer;

use Akeneo\Connectivity\Connection\Domain\Webhook\EventNormalizer\ProductEventNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Platform\Component\EventQueue\Author;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductEventNormalizerSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ProductEventNormalizer::class);
    }

    public function it_supports_a_product_created_event(ProductCreated $event): void
    {
        $this->supports($event)->shouldReturn(true);
    }

    public function it_supports_a_product_updated_event(ProductUpdated $event): void
    {
        $this->supports($event)->shouldReturn(true);
    }

    public function it_supports_a_product_removed_event(ProductRemoved $event): void
    {
        $this->supports($event)->shouldReturn(true);
    }

    public function it_normalizes_a_product_created_event(): void
    {
        $event = new ProductCreated(
            Author::fromNameAndType('julia', Author::TYPE_UI),
            ['identifier' => 'blue_sneakers'],
            0,
            '9979c367-595d-42ad-9070-05f62f31f49b'
        );

        $this->normalize($event)->shouldReturn([
            'action' => 'product.created',
            'event_id' => '9979c367-595d-42ad-9070-05f62f31f49b',
            'event_datetime' => '1970-01-01T00:00:00+00:00',
            'author' => 'julia',
            'author_type' => 'ui',
            'product_identifier' => 'blue_sneakers',
        ]);
    }

    public function it_normalizes_a_product_updated_event(): void
    {
        $event = new ProductUpdated(
            Author::fromNameAndType('julia', Author::TYPE_UI),
            ['identifier' => 'blue_sneakers'],
            0,
            '9979c367-595d-42ad-9070-05f62f31f49b'
        );

        $this->normalize($event)->shouldReturn([
            'action' => 'product.updated',
            'event_id' => '9979c367-595d-42ad-9070-05f62f31f49b',
            'event_datetime' => '1970-01-01T00:00:00+00:00',
            'author' => 'julia',
            'author_type' => 'ui',
            'product_identifier' => 'blue_sneakers',
        ]);
    }

    public function it_normalizes_a_product_removed_event(): void
    {
        $event = new ProductRemoved(
            Author::fromNameAndType('julia', Author::TYPE_UI),
            ['identifier' => 'blue_sneakers', 'category_codes' => []],
            0,
            '9979c367-595d-42ad-9070-05f62f31f49b'
        );

        $this->normalize($event)->shouldReturn([
            'action' => 'product.removed',
            'event_id' => '9979c367-595d-42ad-9070-05f62f31f49b',
            'event_datetime' => '1970-01-01T00:00:00+00:00',
            'author' => 'julia',
            'author_type' => 'ui',
            'product_identifier' => 'blue_sneakers',
        ]);
    }
}
