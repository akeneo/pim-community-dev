<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\EventSubscriber;

use Akeneo\Category\Api\Event\CategoryUpdatedEvent;
use Akeneo\Tool\Bundle\VersioningBundle\Builder\VersionBuilder;
use Akeneo\Tool\Bundle\VersioningBundle\Doctrine\ORM\VersionRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateCategoryVersionSubscriber implements EventSubscriberInterface
{
    const CATEGORY_VERSION_RESOURCE_NAME = "Akeneo\Category\Infrastructure\Component\Model\Category";

    public function __construct(
        private readonly VersionRepository $versionRepository,
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
        $previousVersion = $this->versionRepository->getNewestLogEntry(
            resourceName: self::CATEGORY_VERSION_RESOURCE_NAME,
            resourceId: $event->getCategory()->getId()->getValue(),
            resourceUuid: null
        );

        $newVersion = $this->versionBuilder->buildVersion(
            $event->getCategory(),
            'admin',
            $previousVersion
        );
        // TODO: Maybe use the FactoryVersion and reproduce the logic from the buildVersion() in dedicated categoryVersion service
        //      + avoid normalize process
        //      + take in charge the resourceName when create a version normalize process
        //      - duplicate versionBuild logic => To maintain

        // TODO: Maybe use the FactoryVersion with some change
        //      1 Add ressourceName parameter in the function -> if not null use the resource name given in parameter
        //      2 Create a enrichedCategory symfony normalizer for standard and version
        //      + Keep logic in legacy buildVersion() with minor change
        //      - Create a new normalizer symfony specific for version

        // TODO:
        //  - 1 Get Previous version
        //      (using VersionRepository or using SQL native query and create a Version object with VersionFactory)
        //  - 2 Use BuildVersion() from VersionBuilder
        //      - 2.1 Manage Normalizer to build category snapshot
        //      - 2.2 Manage Ressource name before buildVersion with the factory
        //  - 3 Insert Version in DB

        // TODO: Solution after design meeting
        //  - Create an external service in BC PimVersion
        //  - this service can build a version depending a snapshop (data brut), a resource identifier and a resource name
        //  - This service reproduce the build version logic from the legacy one (changeSet creation).
        //  /!\ Manage to get the previous version ()
        //  /!\ Manage the Uuid
        //      BuildVersionFromID(array snapshot, string resourceName, string resourceId, string author)

        return $event;
    }
}
