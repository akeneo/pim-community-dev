<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Platform\Component\Webhook\EventDataCollection;
use Akeneo\UserManagement\Component\Model\UserInterface;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ProductRemovedEventDataBuilder implements EventDataBuilderInterface
{
    /** @var EventDataBuilderInterface */
    private $eventDataBuilder;

    /** @var CategoryAccessRepository */
    private $categoryAccessRepository;

    public function __construct(
        EventDataBuilderInterface $eventDataBuilder,
        CategoryAccessRepository $categoryAccessRepository
    ) {
        $this->eventDataBuilder = $eventDataBuilder;
        $this->categoryAccessRepository = $categoryAccessRepository;
    }

    public function supports(object $event): bool
    {
        return $this->eventDataBuilder->supports($event);
    }

    /**
     * @param ProductRemoved $event
     */
    public function build(object $event, UserInterface $user): EventDataCollection
    {
        if (false === $this->supports($event)) {
            throw new \InvalidArgumentException();
        }

        $isProductGranted = $this->categoryAccessRepository->isCategoryCodesGranted(
            $user,
            Attributes::VIEW_ITEMS,
            $event->getCategoryCodes()
        );
        if (false === $isProductGranted) {
            throw new NotGrantedProductException($user->getUsername(), $event->getIdentifier());
        }

        return $this->eventDataBuilder->build($event, $user);
    }
}
