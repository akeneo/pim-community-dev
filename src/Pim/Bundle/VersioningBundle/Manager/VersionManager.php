<?php

namespace Pim\Bundle\VersioningBundle\Manager;

use Pim\Bundle\CatalogBundle\Doctrine\SmartManagerRegistry;
use Pim\Bundle\VersioningBundle\Entity\Version;
use Pim\Bundle\VersioningBundle\Builder\VersionBuilder;
use Oro\Bundle\UserBundle\Entity\User;

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

    /**
     * @var integer
     */
    protected $realTimeVersioning = true;

    /**
     * @var string
     */
    protected $username = self::DEFAULT_SYSTEM_USER;

    /**
     * Versioning context
     *
     * @var string|null
     */
    protected $context;

    /**
     * @var SmartManagerRegistry
     */
    protected $registry;

    /**
     * @var VersionBuilder
     */
    protected $versionBuilder;

    /**
     * @param SmartManagerRegistry $registry
     * @param VersionBuilder       $versionBuilder
     */
    public function __construct(SmartManagerRegistry $registry, VersionBuilder $versionBuilder)
    {
        $this->registry       = $registry;
        $this->versionBuilder = $versionBuilder;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @param boolean $mode
     */
    public function setRealTimeVersioning($mode)
    {
        $this->realTimeVersioning = $mode;
    }

    /**
     * @return boolean
     */
    public function isRealTimeVersioning()
    {
        return $this->realTimeVersioning;
    }

    /**
     * Set context
     *
     * @param string $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * Get context
     *
     * @return string|null
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Build a version from a versionable entity
     *
     * @param object $versionable
     * @param array  $changeset
     *
     * @return Version[]
     */
    public function buildVersion($versionable, array $changeset = array())
    {
        $createdVersions = [];

        if ($this->realTimeVersioning) {
            $this->registry->getManagerForClass(get_class($versionable))->refresh($versionable);

            $createdVersions = $this->buildPendingVersions($versionable);

            $builtVersions = array_filter(
                $createdVersions,
                function ($version) {
                    return !empty($version->getChangeset());
                }
            );

            if (!empty($builtVersions)) {
                $previousVersion = end($builtVersions);
            } else {
                $previousVersion = $this->getNewestLogEntry($versionable);
            }

            $createdVersions[] = $this->versionBuilder->buildVersion($versionable, $this->username, $previousVersion, $this->context);
        } else {
            $createdVersions[] = $this->versionBuilder->createPendingVersion($versionable, $this->username, $changeset, $this->context);
        }

        return $createdVersions;
    }

    /**
     * @return VersionRepository
     */
    public function getVersionRepository()
    {
        return $this->registry->getRepository('PimVersioningBundle:Version');
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
        return $this->getVersionRepository()->getLogEntries(get_class($versionable), $versionable->getId());
    }

    /**
     * Return the oldest log entry. A the log is order by date
     * desc, it means the very last line of the log
     *
     * @param object $versionable
     *
     * @return Version|null
     */
    public function getOldestLogEntry($versionable)
    {
        return $this->getVersionRepository()->getOldestLogEntry(get_class($versionable), $versionable->getId());
    }

    /**
     * Return the newest log entry. As the log is order by date
     * desc, it means the first line of the log
     *
     * @param object $versionable
     *
     * @return Version|null
     */
    public function getNewestLogEntry($versionable)
    {
        return $this->getVersionRepository()->getNewestLogEntry(get_class($versionable), $versionable->getId());
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
                ->getNewestLogEntry($pending->getResourceName(), $pending->getResourceId());
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
    protected function buildPendingVersions($versionable)
    {
        $createdVersions = [];

        $pendingVersions = $this->getVersionRepository()->findBy(
            [
                'resourceId'   => $versionable->getId(),
                'resourceName' => get_class($versionable),
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
