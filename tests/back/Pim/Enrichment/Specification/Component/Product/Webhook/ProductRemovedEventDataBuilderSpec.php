<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\ProductRemovedEventDataBuilder;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Platform\Component\Webhook\EventDataCollection;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;

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

    public function it_supports_a_bulk_event_of_product_removed_events(): void
    {
        $bulkEvent = new BulkEvent([
            new ProductRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), ['identifier' => '1', 'category_codes' => []]),
            new ProductRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), ['identifier' => '2', 'category_codes' => []]),
        ]);

        $this->supports($bulkEvent)->shouldReturn(true);
    }

    public function it_does_not_support_a_bulk_event_of_unsupported_product_events(): void
    {
        $bulkEvent = new BulkEvent([
            new ProductCreated(Author::fromNameAndType('julia', Author::TYPE_UI), ['identifier' => '1']),
            new ProductRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), ['identifier' => '1', 'category_codes' => []]),
        ]);

        $this->supports($bulkEvent)->shouldReturn(false);
    }

    public function it_does_not_support_an_individual_event(): void
    {
        $event = new ProductRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), ['identifier' => '1', 'category_codes' => []]);

        $this->supports($event)->shouldReturn(false);
    }

    public function it_builds_a_bulk_event_of_product_removed_event(): void
    {
        $user = new User();
        $user->setId(10);

        $blueJeanEvent = new ProductRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), [
            'identifier' => 'blue_jean',
            'category_codes' => [],
        ]);
        $redJeanEvent = new ProductRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), [
            'identifier' => 'red_jean',
            'category_codes' => [],
        ]);
        $bulkEvent = new BulkEvent([$blueJeanEvent, $redJeanEvent]);

        $expectedCollection = new EventDataCollection();
        $expectedCollection->setEventData($blueJeanEvent, ['resource' => ['identifier' => 'blue_jean']]);
        $expectedCollection->setEventData($redJeanEvent, ['resource' => ['identifier' => 'red_jean']]);

        $collection = $this->build($bulkEvent, $user)->getWrappedObject();

        Assert::assertEquals($expectedCollection, $collection);
    }
}
