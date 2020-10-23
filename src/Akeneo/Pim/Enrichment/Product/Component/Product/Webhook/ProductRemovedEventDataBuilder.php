<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Component\Product\Webhook;

use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
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

    public function supports(BusinessEventInterface $businessEvent): bool
    {
        return $this->eventDataBuilder->supports($businessEvent);
    }

    public function build(BusinessEventInterface $businessEvent, array $context = []): array
    {
        if (false === $this->supports($businessEvent)) {
            throw new \InvalidArgumentException();
        }
        $user = $context['user'] ?? null;
        if (!$user || !$user instanceof UserInterface) {
            throw new \UnexpectedValueException(
                sprintf(
                    '"%s" context must provide a "%s" in the context.',
                    self::class,
                    UserInterface::class
                )
            );
        }
        $categoryCodes = $businessEvent->data()['categories'] ?? null;
        if (!$categoryCodes) {
            throw new \UnexpectedValueException(
                'Business event data must provide a "categories" index.'
            );
        }

        $isProductGranted = $this->categoryAccessRepository->areAllCategoryCodesGranted(
            $user,
            Attributes::VIEW_ITEMS,
            $categoryCodes
        );
        if (!$isProductGranted) {
            $productIdentifier = $businessEvent->data()['identifier'] ?? '';
            throw new NotGrantedProductException($user->getUsername(), $productIdentifier);
        }

        return $this->eventDataBuilder->build($businessEvent);
    }
}
