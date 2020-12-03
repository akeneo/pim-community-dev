<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Product\Component\Product\Webhook\NotGrantedProductException;
use Akeneo\Pim\Enrichment\Product\Component\Product\Webhook\ProductRemovedEventDataBuilder;
use Akeneo\Pim\Enrichment\Product\Component\Product\Query\GetViewableCategoryCodes;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Platform\Component\Webhook\EventDataCollection;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\Assert;
use PhpSpec\ObjectBehavior;

class ProductRemovedEventDataBuilderSpec extends ObjectBehavior
{
    public function let(
        EventDataBuilderInterface $eventDataBuilder,
        GetViewableCategoryCodes $getViewableCategoryCodes
    ): void {
        $this->beConstructedWith($eventDataBuilder, $getViewableCategoryCodes);
    }

    public function it_is_an_event_data_builder(): void
    {
        $this->shouldHaveType(ProductRemovedEventDataBuilder::class);
        $this->shouldImplement(EventDataBuilderInterface::class);
    }

    public function it_supports_the_same_business_events_as_decorated_service($eventDataBuilder): void
    {
        $eventBlueJean = new ProductRemoved(Author::fromNameAndType('erp', 'ui'), $this->aBlueJeanProduct());
        $eventBlueCam = new ProductRemoved(Author::fromNameAndType('erp', 'ui'), $this->aBlueCamProduct());
        $bulkEvent = new BulkEvent([$eventBlueJean, $eventBlueCam]);

        $eventDataBuilder->supports($bulkEvent)->willReturn(true, false);

        $this->supports($bulkEvent)->shouldReturn(true);
        $this->supports($bulkEvent)->shouldReturn(false);
    }

    public function it_handles_permissions_error_for_products_not_viewable(
        $eventDataBuilder,
        $getViewableCategoryCodes
    ): void {
        $user = new User();
        $user->setUsername('erp_1234');
        $user->setId(1234);

        $eventWithGrantedProduct = new ProductRemoved(
            Author::fromNameAndType('erp', 'ui'),
            $this->aBlueJeanProduct()
        );

        $eventWithNonGrantedProduct = new ProductRemoved(
            Author::fromNameAndType('erp', 'ui'),
            $this->aBlueCamProduct()
        );

        $bulkEvent = new BulkEvent(
            [
                $eventWithGrantedProduct,
                $eventWithNonGrantedProduct,
            ]
        );

        $eventDataBuilder->supports($bulkEvent)->willReturn(true);

        $getViewableCategoryCodes->forCategoryCodes(
            $user->getId(),
            $eventWithGrantedProduct->getCategoryCodes()
        )->willReturn($eventWithGrantedProduct->getCategoryCodes());

        $getViewableCategoryCodes->forCategoryCodes(
            $user->getId(),
            $eventWithNonGrantedProduct->getCategoryCodes()
        )->willReturn([]);

        $expectedCollection = new EventDataCollection();
        $expectedCollection->setEventData($eventWithGrantedProduct, ['resource' => ['identifier' => 'blue_jean']]);
        $expectedCollection->setEventDataError(
            $eventWithNonGrantedProduct,
            new NotGrantedProductException($user->getUsername(), 'blue_cam')
        );

        $actualCollection = $this->build($bulkEvent, $user);

        Assert::assertEquals($expectedCollection, $actualCollection->getWrappedObject());
    }

    public function it_throws_an_error_if_an_event_is_not_supported($eventDataBuilder): void
    {
        $user = new User();
        $user->setUsername('erp_1234');

        $supportedEvent = new ProductRemoved(
            Author::fromNameAndType('erp', 'ui'),
            $this->aBlueJeanProduct()
        );

        $notSupportedEvent = new ProductRemoved(
            Author::fromNameAndType('erp', 'ui'),
            $this->aBlueCamProduct()
        );

        $bulkEvent = new BulkEvent(
            [
                $supportedEvent,
                $notSupportedEvent,
            ]
        );

        $eventDataBuilder->supports($bulkEvent)->willReturn(false);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('build', [$bulkEvent, $user]);
    }

    private function aBlueJeanProduct(): array
    {
        return [
            'identifier' => 'blue_jean',
            'category_codes' => ['clothes'],
        ];
    }

    private function aBlueCamProduct(): array
    {
        return [
            'identifier' => 'blue_cam',
            'category_codes' => ['cameras'],
        ];
    }
}
