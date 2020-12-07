<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\ProductModelRemovedEventDataBuilder as BaseProductModelRemovedEventDataBuilder;
use Akeneo\Pim\Enrichment\Product\Component\Product\Webhook\NotGrantedProductModelException;
use Akeneo\Pim\Enrichment\Product\Component\Product\Webhook\ProductModelRemovedEventDataBuilder;
use Akeneo\Pim\Enrichment\Product\Component\Product\Query\GetViewableCategoryCodes;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Platform\Component\Webhook\EventDataCollection;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\Assert;
use PhpSpec\ObjectBehavior;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelRemovedEventDataBuilderSpec extends ObjectBehavior
{
    public function let(
        BaseProductModelRemovedEventDataBuilder $baseProductModelRemovedEventDataBuilder,
        GetViewableCategoryCodes $getViewableCategoryCodes
    ): void {
        $this->beConstructedWith($baseProductModelRemovedEventDataBuilder, $getViewableCategoryCodes);
    }

    public function it_is_an_event_data_builder(): void
    {
        $this->shouldHaveType(ProductModelRemovedEventDataBuilder::class);
        $this->shouldImplement(EventDataBuilderInterface::class);
    }

    public function it_supports_the_same_business_events_as_decorated_service($baseProductModelRemovedEventDataBuilder
    ): void {
        $eventBlueJean = new ProductModelRemoved(Author::fromNameAndType('erp', 'ui'), $this->aBlueJeanProductModel());
        $eventBlueCam = new ProductModelRemoved(Author::fromNameAndType('erp', 'ui'), $this->aBlueCamProductModel());
        $bulkEvent = new BulkEvent([$eventBlueJean, $eventBlueCam]);

        $baseProductModelRemovedEventDataBuilder->supports($bulkEvent)->willReturn(true, false);
        
        $this->supports($bulkEvent)->shouldReturn(true);
        $this->supports($bulkEvent)->shouldReturn(false);
    }

    public function it_handles_permissions_error_for_products_model_not_viewable(
        $baseProductModelRemovedEventDataBuilder,
        $getViewableCategoryCodes
    ): void {
        $user = new User();
        $user->setUsername('erp_1234');
        $user->setId(1234);

        $eventWithGrantedProductModel = new ProductModelRemoved(
            Author::fromNameAndType('erp', 'ui'),
            $this->aBlueJeanProductModel()
        );

        $eventWithNonGrantedProductModel = new ProductModelRemoved(
            Author::fromNameAndType('erp', 'ui'),
            $this->aBlueCamProductModel()
        );

        $bulkEvent = new BulkEvent(
            [
                $eventWithGrantedProductModel,
                $eventWithNonGrantedProductModel,
            ]
        );

        $baseProductModelRemovedEventDataBuilder->supports($bulkEvent)->willReturn(true);

        $getViewableCategoryCodes->forCategoryCodes(
            $user->getId(),
            $eventWithGrantedProductModel->getCategoryCodes()
        )->willReturn($eventWithGrantedProductModel->getCategoryCodes());

        $getViewableCategoryCodes->forCategoryCodes(
            $user->getId(),
            $eventWithNonGrantedProductModel->getCategoryCodes()
        )->willReturn([]);

        $expectedCollection = new EventDataCollection();
        $expectedCollection->setEventData($eventWithGrantedProductModel, ['resource' => ['code' => 'blue_jean']]);
        $expectedCollection->setEventDataError(
            $eventWithNonGrantedProductModel,
            new NotGrantedProductModelException($user->getUsername(), 'blue_cam')
        );

        $actualCollection = $this->build($bulkEvent, $user);

        Assert::assertEquals($expectedCollection, $actualCollection->getWrappedObject());
    }

    public function it_throws_an_error_if_an_event_is_not_supported($baseProductModelRemovedEventDataBuilder): void
    {
        $user = new User();
        $user->setUsername('erp_1234');

        $supportedEvent = new ProductRemoved(
            Author::fromNameAndType('erp', 'ui'),
            $this->aBlueJeanProductModel()
        );

        $notSupportedEvent = new ProductRemoved(
            Author::fromNameAndType('erp', 'ui'),
            $this->aBlueCamProductModel()
        );

        $bulkEvent = new BulkEvent(
            [
                $supportedEvent,
                $notSupportedEvent,
            ]
        );

        $baseProductModelRemovedEventDataBuilder->supports($bulkEvent)->willReturn(false);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('build', [$bulkEvent, $user]);
    }

    private function aBlueJeanProductModel(): array
    {
        return [
            'identifier' => 'blue_jean',
            'code' => 'blue_jean',
            'category_codes' => ['clothes'],
        ];
    }

    private function aBlueCamProductModel(): array
    {
        return [
            'identifier' => 'blue_cam',
            'code' => 'blue_cam',
            'category_codes' => ['cameras'],
        ];
    }
}
