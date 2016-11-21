<?php

namespace Pim\Bundle\VersioningBundle\Manager;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\SmartManagerRegistry;
use Akeneo\Component\Versioning\Model\Version;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\VersioningBundle\Builder\VersionBuilder;
use Pim\Bundle\VersioningBundle\Event\BuildVersionEvent;
use Pim\Bundle\VersioningBundle\Event\BuildVersionEvents;
use Pim\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
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

    /** @var SmartManagerRegistry */
    protected $registry;

    /** @var VersionBuilder */
    protected $versionBuilder;

    /** @var VersionContext */
    protected $versionContext;

    /**
     * @param SmartManagerRegistry     $registry
     * @param VersionBuilder           $versionBuilder
     * @param VersionContext           $versionContext
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        SmartManagerRegistry $registry,
        VersionBuilder $versionBuilder,
        VersionContext $versionContext,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->registry = $registry;
        $this->versionBuilder = $versionBuilder;
        $this->versionContext = $versionContext;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param bool $mode
     */
    public function setRealTimeVersioning($mode)
    {
        $this->realTimeVersioning = $mode;
    }

    /**
     * @return bool
     */
    public function isRealTimeVersioning()
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
    public function buildVersion($versionable, array $changeset = [])
    {
        $createdVersions = [];

        $event = $this->eventDispatcher->dispatch(BuildVersionEvents::PRE_BUILD, new BuildVersionEvent());
        if (null !== $event && null !== $event->getUsername()) {
            $this->username = $event->getUsername();
        }

        if ($this->realTimeVersioning) {
            $manager = $this->registry->getManagerForClass(ClassUtils::getClass($versionable));
            $manager->refresh($versionable);

            $createdVersions = $this->buildPendingVersions($versionable);

            $builtVersions = array_filter(
                $createdVersions,
                function ($version) {
                    return count($version->getChangeset()) > 0;
                }
            );

            if (!empty($builtVersions)) {
                $previousVersion = end($builtVersions);
            } else {
                $previousVersion = $this->getNewestLogEntry($versionable);
            }

            $createdVersions[] = $this->versionBuilder
                ->buildVersion(
                    $versionable,
                    $this->username,
                    $previousVersion,
                    $this->versionContext->getContextInfo(ClassUtils::getClass($versionable))
                );

            if (null !== $previousVersion) {
                $manager->detach($previousVersion);
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
     *
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->registry->getManagerForClass('Akeneo\\Component\\Versioning\\Model\\Version');
    }

    /**
     * @return VersionRepositoryInterface
     */
    public function getVersionRepository()
    {
        return $this->registry->getRepository('Akeneo\Component\Versioning\Model\Version');
    }

    /**
     * Return log entries
     *
     * @param object $versionable
     *
     * @return ArrayCollection
     */
    public function getLogEntries($versionable)
    {
        return $this->getVersionRepository()->getLogEntries(ClassUtils::getClass($versionable), $versionable->getId());
    }

    /**
     * Return the oldest log entry. A the log is order by date
     * desc, it means the very last line of the log
     *
     * @param object    $versionable
     * @param null|bool $pending
     *
     * @return Version|null
     */
    public function getOldestLogEntry($versionable, $pending = false)
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
     *
     * @return Version|null
     */
    public function getNewestLogEntry($versionable, $pending = false)
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
     *
     * @return Version
     */
    public function buildPendingVersion(Version $pending, Version $previousVersion = null)
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
    public function buildPendingVersions($versionable)
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
            if ($version->getChangeset()) {
                $previousVersion = $version;
            }
        }

        return $createdVersions;
    }
}
