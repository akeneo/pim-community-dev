<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Webhook\EventNormalizer;

use Akeneo\Connectivity\Connection\Domain\Webhook\EventNormalizer\ProductModelEventNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Platform\Component\EventQueue\Author;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelEventNormalizerSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ProductModelEventNormalizer::class);
    }

    public function it_supports_a_product_model_created_event(ProductModelCreated $event): void
    {
        $this->supports($event)->shouldReturn(true);
    }

    public function it_supports_a_product_model_updated_event(ProductModelUpdated $event): void
    {
        $this->supports($event)->shouldReturn(true);
    }

    public function it_supports_a_product_model_removed_event(ProductModelRemoved $event): void
    {
        $this->supports($event)->shouldReturn(true);
    }

    public function it_normalizes_a_product_model_created_event(): void
    {
        $event = new ProductModelCreated(
            Author::fromNameAndType('julia', Author::TYPE_UI),
            ['code' => 'sneakers'],
            0,
            '9979c367-595d-42ad-9070-05f62f31f49b'
        );

        $this->normalize($event)->shouldReturn([
            'action' => 'product_model.created',
            'event_id' => '9979c367-595d-42ad-9070-05f62f31f49b',
            'event_datetime' => '1970-01-01T00:00:00+00:00',
            'author' => 'julia',
            'author_type' => 'ui',
            'product_model_code' => 'sneakers',
        ]);
    }

    public function it_normalizes_a_product_model_updated_event(): void
    {
        $event = new ProductModelUpdated(
            Author::fromNameAndType('julia', Author::TYPE_UI),
            ['code' => 'sneakers'],
            0,
            '9979c367-595d-42ad-9070-05f62f31f49b'
        );

        $this->normalize($event)->shouldReturn([
            'action' => 'product_model.updated',
            'event_id' => '9979c367-595d-42ad-9070-05f62f31f49b',
            'event_datetime' => '1970-01-01T00:00:00+00:00',
            'author' => 'julia',
            'author_type' => 'ui',
            'product_model_code' => 'sneakers',
        ]);
    }

    public function it_normalizes_a_product_model_removed_event(): void
    {
        $event = new ProductModelRemoved(
            Author::fromNameAndType('julia', Author::TYPE_UI),
            ['code' => 'sneakers', 'category_codes' => []],
            0,
            '9979c367-595d-42ad-9070-05f62f31f49b'
        );

        $this->normalize($event)->shouldReturn([
            'action' => 'product_model.removed',
            'event_id' => '9979c367-595d-42ad-9070-05f62f31f49b',
            'event_datetime' => '1970-01-01T00:00:00+00:00',
            'author' => 'julia',
            'author_type' => 'ui',
            'product_model_code' => 'sneakers',
        ]);
    }
}
