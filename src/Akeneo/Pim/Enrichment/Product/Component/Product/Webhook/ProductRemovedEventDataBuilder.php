<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Component\Product\Webhook;

use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Platform\Component\EventQueue\EventInterface;
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
     * @param EventInterface $event
     */
    public function build(object $event, UserInterface $user): EventDataCollection
    {
        if (false === $this->supports($event)) {
            throw new \InvalidArgumentException();
        }

        $categoryCodes = $event->getData()['categories'] ?? null;
        if (null === $categoryCodes) {
            throw new \UnexpectedValueException(
                'Business event data must provide a "categories" index.'
            );
        }

        $isProductGranted = $this->categoryAccessRepository->isCategoryCodesGranted(
            $user,
            Attributes::VIEW_ITEMS,
            $categoryCodes
        );
        if (false === $isProductGranted) {
            $productIdentifier = $event->getData()['identifier'] ?? '';
            throw new NotGrantedProductException($user->getUsername(), $productIdentifier);
        }

        return $this->eventDataBuilder->build($event, $user);
    }
}
