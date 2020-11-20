<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Product\Component\Product\Webhook\NotGrantedProductException;
use Akeneo\Pim\Enrichment\Product\Component\Product\Webhook\ProductRemovedEventDataBuilder;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Platform\Component\Webhook\EventDataCollection;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;

class ProductRemovedEventDataBuilderSpec extends ObjectBehavior
{
    public function let(
        EventDataBuilderInterface $eventDataBuilder,
        CategoryAccessRepository $categoryAccessRepository
    ): void {
        $this->beConstructedWith($eventDataBuilder, $categoryAccessRepository);
    }

    public function it_is_an_event_data_builder(): void
    {
        $this->shouldHaveType(ProductRemovedEventDataBuilder::class);
        $this->shouldImplement(EventDataBuilderInterface::class);
    }

    public function it_supports_the_same_business_events_as_decorated_service($eventDataBuilder): void
    {
        $event = new ProductRemoved(Author::fromNameAndType('erp', 'ui'), $this->getProduct());
        $eventDataBuilder->supports($event)->willReturn(true, false);

        $this->supports($event)->shouldReturn(true);
        $this->supports($event)->shouldReturn(false);
    }

    public function it_builds_event_data_if_the_product_is_granted(
        $eventDataBuilder,
        $categoryAccessRepository
    ): void {
        $user = new User();
        $event = new ProductRemoved(Author::fromNameAndType('erp', 'ui'), $this->getProduct());
        $eventDataBuilder->supports($event)->willReturn(true);

        $categoryAccessRepository->isCategoryCodesGranted(
            $user,
            Attributes::VIEW_ITEMS,
            ['clothes']
        )->willReturn(true);

        $collection = new EventDataCollection();
        $collection->setEventData($event, ['resource' => ['identifier' => 'blue_jean']]);

        $eventDataBuilder->build($event, $user)->willReturn($collection);

        $this->build($event, $user);
    }

    public function it_does_not_build_event_data_if_the_product_is_not_granted(
        $eventDataBuilder,
        $categoryAccessRepository
    ): void {
        $user = new User();
        $user->setUsername('erp_06458');
        $event = new ProductRemoved(Author::fromNameAndType('erp', 'ui'), $this->getProduct());
        $eventDataBuilder->supports($event)->willReturn(true);

        $categoryAccessRepository->isCategoryCodesGranted(
            $user,
            Attributes::VIEW_ITEMS,
            ['clothes']
        )->willReturn(false);

        $eventDataBuilder->build($event)->willReturn(['resource' => ['identifier' => 'blue_jean']]);

        $this
            ->shouldThrow(NotGrantedProductException::class)
            ->during('build', [$event, $user]);
    }

    public function it_throws_an_error_if_the_business_event_is_not_supported($eventDataBuilder): void
    {
        $user = new User();
        $event = new ProductRemoved(Author::fromNameAndType('erp', 'ui'), $this->getProduct());
        $eventDataBuilder->supports($event)->willReturn(false);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('build', [$event, $user]);
    }

    private function getProduct(): array
    {
        return [
            'identifier' => 'blue_jean',
            'category_codes' => ['clothes'],
        ];
    }
}
