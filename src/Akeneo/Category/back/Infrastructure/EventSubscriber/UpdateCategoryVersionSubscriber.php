<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\EventSubscriber;

use Akeneo\Category\Domain\Event\CategoryUpdatedEvent;
use Akeneo\Category\Infrastructure\Builder\CategoryVersionBuilder;
use Akeneo\Tool\Bundle\VersioningBundle\ServiceApi\VersionBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateCategoryVersionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly VersionBuilder $versionBuilder,
        private readonly CategoryVersionBuilder $categoryVersionBuilder,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CategoryUpdatedEvent::class => 'UpdateCategoryVersion',
        ];
    }

    public function updateCategoryVersion(CategoryUpdatedEvent $event): void
    {
        $categoryVersion = $this->categoryVersionBuilder->create($event->getCategory());

        $this->versionBuilder->buildVersionWithId(
            resourceId: $categoryVersion->getResourceId(),
            resourceName: $categoryVersion->getResourceName(),
            snapshot: $categoryVersion->getSnapshot(),
        );
    }
}
