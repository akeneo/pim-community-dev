<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Manager;

use Doctrine\Common\Persistence\ObjectRepository;
use Akeneo\Tool\Bundle\VersioningBundle\Builder\VersionBuilder;
use Akeneo\Tool\Bundle\VersioningBundle\Event\BuildVersionEvent;
use Akeneo\Tool\Bundle\VersioningBundle\Event\BuildVersionEvents;
use Akeneo\Tool\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
use Akeneo\Tool\Component\Versioning\Model\Version;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Version manager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionManager
{
    /**
     * Default system user
     *
     * @var string
     */
    const DEFAULT_SYSTEM_USER = 'admin';

    /** @var bool */
    protected $realTimeVersioning = true;

    /** @var string */
    protected $username = self::DEFAULT_SYSTEM_USER;

    /**
     * Versioning context
     *
     * @var array
     */
    protected $context;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var VersionBuilder */
    protected $versionBuilder;

    /** @var VersionContext */
    protected $versionContext;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param ObjectManager            $objectManager
     * @param VersionBuilder           $versionBuilder
     * @param VersionContext           $versionContext
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        ObjectManager $objectManager,
        VersionBuilder $versionBuilder,
        VersionContext $versionContext,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->objectManager = $objectManager;
        $this->versionBuilder = $versionBuilder;
        $this->versionContext = $versionContext;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param bool $mode
     */
    public function setRealTimeVersioning(bool $mode): void
    {
        $this->realTimeVersioning = $mode;
    }

    public function isRealTimeVersioning(): bool
    {
        return $this->realTimeVersioning;
    }

    /**
     * Build a version from a versionable entity
     *
     * @param object $versionable
     * @param array  $changeset
     *
     * @return Version[]
     */
    public function buildVersion(object $versionable, array $changeset = []): array
    {
        $createdVersions = [];

        $event = $this->eventDispatcher->dispatch(BuildVersionEvents::PRE_BUILD, new BuildVersionEvent());
        if (null !== $event && null !== $event->getUsername()) {
            $this->username = $event->getUsername();
        }

        if ($this->realTimeVersioning) {
            $createdVersions = $this->buildPendingVersions($versionable);

            $builtVersions = array_filter(
                $createdVersions,
                fn($version) => count($version->getChangeset()) > 0
            );

            $previousVersion = !empty($builtVersions) ? end($builtVersions) : $this->getNewestLogEntry($versionable);

            $createdVersions[] = $this->versionBuilder
                ->buildVersion(
                    $versionable,
                    $this->username,
                    $previousVersion,
                    $this->versionContext->getContextInfo(ClassUtils::getClass($versionable))
                );

            if (null !== $previousVersion) {
                $this->objectManager->detach($previousVersion);
            }
        } else {
            $createdVersions[] = $this->versionBuilder
                ->createPendingVersion(
                    $versionable,
                    $this->username,
                    $changeset,
                    $this->versionContext->getContextInfo(ClassUtils::getClass($versionable))
                );
        }

        return $createdVersions;
    }

    /**
     * Get object manager for Version
     */
    public function getObjectManager(): \Doctrine\Common\Persistence\ObjectManager
    {
        return $this->objectManager;
    }

    public function getVersionRepository(): ObjectRepository
    {
        return $this->objectManager->getRepository(Version::class);
    }

    /**
     * Return log entries
     *
     * @param object $versionable
     */
    public function getLogEntries(object $versionable): ?array
    {
        return $this->getVersionRepository()->getLogEntries(ClassUtils::getClass($versionable), $versionable->getId());
    }

    /**
     * Return the oldest log entry. A the log is order by date
     * desc, it means the very last line of the log
     *
     * @param object    $versionable
     * @param null|bool $pending
     */
    public function getOldestLogEntry(object $versionable, ?bool $pending = false): ?\Akeneo\Tool\Component\Versioning\Model\Version
    {
        return $this->getVersionRepository()->getOldestLogEntry(
            ClassUtils::getClass($versionable),
            $versionable->getId(),
            $pending
        );
    }

    /**
     * Return the newest log entry. As the log is order by date
     * desc, it means the first line of the log
     *
     * @param object    $versionable
     * @param null|bool $pending
     */
    public function getNewestLogEntry(object $versionable, ?bool $pending = false): ?\Akeneo\Tool\Component\Versioning\Model\Version
    {
        return $this->getVersionRepository()->getNewestLogEntry(
            ClassUtils::getClass($versionable),
            $versionable->getId(),
            $pending
        );
    }

    /**
     * Build a pending version
     *
     * @param Version      $pending
     * @param Version|null $previousVersion
     */
    public function buildPendingVersion(Version $pending, Version $previousVersion = null): \Akeneo\Tool\Component\Versioning\Model\Version
    {
        if (null === $previousVersion) {
            $previousVersion = $this->getVersionRepository()
                ->getNewestLogEntry($pending->getResourceName(), $pending->getResourceId(), false);
        }

        return $this->versionBuilder->buildPendingVersion($pending, $previousVersion);
    }

    /**
     * Build pending versions for a single versionable entity
     *
     * @param object $versionable
     *
     * @return Version[]
     */
    public function buildPendingVersions(object $versionable): array
    {
        $createdVersions = [];

        $pendingVersions = $this->getVersionRepository()->findBy(
            [
                'resourceId'   => $versionable->getId(),
                'resourceName' => ClassUtils::getClass($versionable),
                'pending'      => true
            ],
            ['loggedAt' => 'asc']
        );

        $previousVersion = null;
        foreach ($pendingVersions as $pending) {
            $version = $this->buildPendingVersion($pending, $previousVersion);
            $createdVersions[] = $version;
            if ($version->getChangeset() !== []) {
                $previousVersion = $version;
            }
        }

        return $createdVersions;
    }
}
