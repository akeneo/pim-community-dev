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
            ['space_battleship']
        )->willReturn(true);

        $eventDataBuilder->build($event, $user)->willReturn(['resource' => ['identifier' => 'cruiser']]);

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
            ['space_battleship']
        )->willReturn(false);

        $eventDataBuilder->build($event)->willReturn(['resource' => ['identifier' => 'cruiser']]);

        $this
            ->shouldThrow(NotGrantedProductException::class)
            ->during('build', [$event, $user]);
    }

    public function it_throws_an_error_if_the_business_event_data_does_not_provide_categories(
        $eventDataBuilder
    ): void {
        $user = new User();
        $event = new ProductRemoved(Author::fromNameAndType('erp', 'ui'), []);
        $eventDataBuilder->supports($event)->willReturn(true);

        $this
            ->shouldThrow(\UnexpectedValueException::class)
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
            'identifier' => 'cruiser',
            'family' => 'battleship',
            'parent' => null,
            'groups' => [],
            'categories' => ['space_battleship'],
            'enabled' => true,
            'values' => ['sku' => [['locale' => null, 'scope' => null, 'data' => 'battleship']]],
            'created' => '2016-06-14T13:12:50+02:00',
            'updated' => '2016-06-14T13:12:50+02:00',
        ];
    }
}
