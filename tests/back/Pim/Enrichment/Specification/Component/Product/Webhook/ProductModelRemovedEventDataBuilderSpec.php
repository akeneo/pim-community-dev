<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\ProductModelRemovedEventDataBuilder;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Platform\Component\Webhook\EventDataCollection;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;

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

    public function it_supports_a_bulk_event_of_product_removed_events(): void
    {
        $bulkEvent = new BulkEvent([
            new ProductModelRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), ['code' => '1', 'category_codes' => []]),
            new ProductModelRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), ['code' => '2', 'category_codes' => []]),
        ]);

        $this->supports($bulkEvent)->shouldReturn(true);
    }

    public function it_does_not_support_a_bulk_event_of_unsupported_product_events(): void
    {
        $bulkEvent = new BulkEvent([
            new ProductModelRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), ['code' => '1', 'category_codes' => []]),
            new ProductRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), ['identifier' => '1', 'category_codes' => []]),
        ]);

        $this->supports($bulkEvent)->shouldReturn(false);
    }

    public function it_does_not_support_an_individual_event(): void
    {
        $event = new ProductModelRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), ['code' => '1', 'category_codes' => []]);

        $this->supports($event)->shouldReturn(false);
    }

    public function it_builds_a_bulk_event_of_product_removed_event(): void
    {
        $user = new User();
        $user->setId(10);

        $blueJeanEvent = new ProductModelRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), [
            'code' => 'blue_jean',
            'category_codes' => [],
        ]);
        $redJeanEvent = new ProductModelRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), [
            'code' => 'red_jean',
            'category_codes' => [],
        ]);
        $bulkEvent = new BulkEvent([$blueJeanEvent, $redJeanEvent]);

        $expectedCollection = new EventDataCollection();
        $expectedCollection->setEventData($blueJeanEvent, ['resource' => ['code' => 'blue_jean']]);
        $expectedCollection->setEventData($redJeanEvent, ['resource' => ['code' => 'red_jean']]);

        $collection = $this->build($bulkEvent, $user)->getWrappedObject();

        Assert::assertEquals($expectedCollection, $collection);
    }
}
