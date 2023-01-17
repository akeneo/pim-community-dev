<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\EventSubscriber;

use Akeneo\Category\Api\Event\CategoryUpdatedEvent;
use Akeneo\Category\Infrastructure\Builder\CategoryVersionBuilder;
use Akeneo\Tool\Bundle\VersioningBundle\ServiceApi\VersionBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateCategoryVersionSubscriber implements EventSubscriberInterface
{
    public const CATEGORY_VERSION_RESOURCE_NAME = "Akeneo\Category\Infrastructure\Component\Model\Category";

    public function __construct(
        private readonly VersionBuilder $versionBuilder,
        private readonly CategoryVersionBuilder $categoryVersionBuilder,
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            CategoryUpdatedEvent::class => 'UpdateCategoryVersion',
        ];
    }

    public function UpdateCategoryVersion(CategoryUpdatedEvent $event): void
    {
        $categorySnapshot = $this->categoryVersionBuilder->create($event->getCategory());

        $this->versionBuilder->buildVersionWithId(
            resourceId: strval($event->getCategory()->getId()->getValue()),
            resourceName: self::CATEGORY_VERSION_RESOURCE_NAME,
            snapshot: $categorySnapshot,
        );

        // TODO: Solution after design meeting
        //  - Create an external service in BC PimVersion
        //  - this service can build a version depending a snapshop (data brut), a resource identifier and a resource name
        //  - This service reproduce the build version logic from the legacy one (changeSet creation).
        //  /!\ Manage to get the previous version ()
        //  /!\ Manage the Uuid
        //      BuildVersionFromID(array snapshot, string resourceName, string resourceId, string author)

        // TODO:
        //  - Test subscriber
        //  - Author management
        //  - Uuid management
        //  - version saving in db

        //  - context management
        //  - pending status management
        //  - version management event generation
        //  - Update existing Category
    }
}
