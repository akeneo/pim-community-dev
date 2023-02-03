<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\VersioningBundle\ServiceApi;

use Akeneo\Tool\Bundle\VersioningBundle\Builder\VersionBuilder as LegacyVersionBuilder;
use Akeneo\Tool\Bundle\VersioningBundle\Doctrine\ORM\VersionRepository;
use Akeneo\Tool\Bundle\VersioningBundle\Event\BuildVersionEvent;
use Akeneo\Tool\Bundle\VersioningBundle\Event\BuildVersionEvents;
use Akeneo\Tool\Bundle\VersioningBundle\Factory\VersionFactory;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Tool\Component\Versioning\Model\Version;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionBuilder
{
    public function __construct(
        private readonly VersionFactory $versionFactory,
        private readonly VersionRepository $versionRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ObjectManager $objectManager,
        private readonly LegacyVersionBuilder $versionBuilder
    ) {
    }

    public function buildVersionWithId(?string $resourceId, string $resourceName, array $snapshot): void
    {
        $username = $this->getUsername();

        $previousVersion = $this->versionRepository->getNewestLogEntry(
            resourceName: $resourceName,
            resourceId: $resourceId,
            resourceUuid: null
        );
        $versionNumber = $previousVersion ? $previousVersion->getVersion() + 1 : 1;
        $oldSnapshot = $previousVersion ? $previousVersion->getSnapshot() : [];

        $changeset = $this->versionBuilder->buildChangeset($oldSnapshot, $snapshot);

        $versions = [];
        $version = $this->versionFactory->create($resourceName, $resourceId, null, $username);
        $version->setVersion($versionNumber)
            ->setSnapshot($snapshot)
            ->setChangeset($changeset);
        $versions[] = $version;

        // To be compliant with legacy history versioning,
        // Only when updating entity, if changeset has any permission : create a dedicated version with only updated value as a changeset (GRF-671)
        // This logic Will be moved to each BC when Changeset will be manage (GRF-695))
        if ($this->hasPermission($changeset) && null !== $resourceId) {
            $dedicatedVersion = $this->buildPermissionVersion($version);
            unset($changeset['updated']);
            $version->setChangeset($changeset);
            $versions[] = $dedicatedVersion;
        }

        foreach ($versions as $versionToSave) {
            $this->computeChangeSet($versionToSave);
        }
    }

    private function computeChangeSet(Version $version): void
    {
        if ($version->getChangeset()) {
            $this->objectManager->persist($version);
        } else {
            $this->objectManager->remove($version);
        }
        $this->objectManager->flush();
    }

    private function getUsername(): string
    {
        $username = VersionManager::DEFAULT_SYSTEM_USER;
        $event = $this->eventDispatcher->dispatch(new BuildVersionEvent(), BuildVersionEvents::PRE_BUILD);

        if (null !== $event->getUsername()) {
            $username = $event->getUsername();
        }
        return $username;
    }

    /**
     * Check if a changeset has any permissions.
     *
     * @param array $changeset The changeset to check
     * @return bool true if a changeset contains any permissions
     */
    private function hasPermission(array $changeset): bool
    {
        $changesetPermissions = array_filter($changeset, function ($key) {
            return str_ends_with($key, '_permission');
        }, ARRAY_FILTER_USE_KEY);

        return count($changesetPermissions) > 0;
    }

    /**
     * Add a dedicated version when original version contains any changeset permission
     *
     * @param Version $version
     * @return Version
     */
    private function buildPermissionVersion(Version $version): Version
    {
        $updatedVersion = $this->versionFactory->create(
            $version->getResourceName(),
            $version->getResourceId(),
            null,
            $version->getAuthor()
        );
        $updatedVersion->setVersion($version->getVersion() + 1)
            ->setSnapshot($version->getSnapshot())
            ->setChangeset(['updated' => $version->getChangeset()['updated']]);

        return $updatedVersion;
    }
}
