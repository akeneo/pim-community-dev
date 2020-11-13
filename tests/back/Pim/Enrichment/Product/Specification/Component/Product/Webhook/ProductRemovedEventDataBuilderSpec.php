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
        $businessEvent = new ProductRemoved(Author::fromNameAndType('erp', 'ui'), $this->getProduct());
        $eventDataBuilder->supports($businessEvent)->willReturn(true, false);

        $this->supports($businessEvent)->shouldReturn(true);
        $this->supports($businessEvent)->shouldReturn(false);
    }

    public function it_builds_event_data_if_the_product_is_granted(
        $eventDataBuilder,
        $categoryAccessRepository
    ): void {
        $user = new User();
        $businessEvent = new ProductRemoved(Author::fromNameAndType('erp', 'ui'), $this->getProduct());
        $eventDataBuilder->supports($businessEvent)->willReturn(true);

        $categoryAccessRepository->isCategoryCodesGranted(
            $user,
            Attributes::VIEW_ITEMS,
            ['space_battleship']
        )->willReturn(true);

        $eventDataBuilder->build($businessEvent)->willReturn(['resource' => ['identifier' => 'cruiser']]);

        $this->build($businessEvent, ['user' => $user]);
    }

    public function it_does_not_build_event_data_if_the_product_is_not_granted(
        $eventDataBuilder,
        $categoryAccessRepository
    ): void {
        $user = new User();
        $user->setUsername('erp_06458');
        $businessEvent = new ProductRemoved(Author::fromNameAndType('erp', 'ui'), $this->getProduct());
        $eventDataBuilder->supports($businessEvent)->willReturn(true);

        $categoryAccessRepository->isCategoryCodesGranted(
            $user,
            Attributes::VIEW_ITEMS,
            ['space_battleship']
        )->willReturn(false);

        $eventDataBuilder->build($businessEvent)->willReturn(['resource' => ['identifier' => 'cruiser']]);

        $this
            ->shouldThrow(NotGrantedProductException::class)
            ->during('build', [$businessEvent, ['user' => $user]]);
    }

    public function it_throws_an_error_if_the_business_event_data_does_not_provide_categories(
        $eventDataBuilder
    ): void {
        $user = new User();
        $businessEvent = new ProductRemoved(Author::fromNameAndType('erp', 'ui'), []);
        $eventDataBuilder->supports($businessEvent)->willReturn(true);

        $this
            ->shouldThrow(\UnexpectedValueException::class)
            ->during('build', [$businessEvent, ['user' => $user]]);
    }

    public function it_throws_an_error_if_the_business_event_is_not_supported($eventDataBuilder): void
    {
        $businessEvent = new ProductRemoved(Author::fromNameAndType('erp', 'ui'), $this->getProduct());
        $eventDataBuilder->supports($businessEvent)->willReturn(false);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('build', [$businessEvent]);
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
