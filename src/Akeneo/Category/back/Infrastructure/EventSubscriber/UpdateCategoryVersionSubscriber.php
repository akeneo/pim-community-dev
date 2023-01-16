<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\EventSubscriber;

use Akeneo\Category\Api\Event\CategoryUpdatedEvent;
use Akeneo\Tool\Bundle\VersioningBundle\ServiceApi\VersionBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateCategoryVersionSubscriber implements EventSubscriberInterface
{
    const CATEGORY_VERSION_RESOURCE_NAME = "Akeneo\Category\Infrastructure\Component\Model\Category";

    public function __construct(
        private readonly VersionBuilder $versionBuilder
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            CategoryUpdatedEvent::class => 'UpdateCategoryVersion',
        ];
    }

    public function UpdateCategoryVersion(CategoryUpdatedEvent $event): CategoryUpdatedEvent
    {


        $newVersion = $this->versionBuilder->buildVersionWithId(
            $event->getCategory(),
            'admin',
            $previousVersion
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

        return $event;
    }
}
