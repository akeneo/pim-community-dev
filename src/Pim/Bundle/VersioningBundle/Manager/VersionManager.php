<?php

namespace Pim\Bundle\VersioningBundle\Manager;

use Pim\Bundle\CatalogBundle\Doctrine\SmartManagerRegistry;
use Pim\Bundle\VersioningBundle\Entity\Version;
use Pim\Bundle\VersioningBundle\Entity\Pending;
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
     * @var User
     */
    protected $user;

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
     * @param User|null $user
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
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
     *
     * @return Version|Pending
     */
    public function buildVersion($versionable)
    {
        $user = $this->getUser();

        if ($this->realTimeVersioning) {
            $this->registry->getManagerForClass(get_class($versionable))->refresh($versionable);

            if ($user) {
                $previousVersion = $this->getVersionRepository()
                    ->getNewestLogEntry(get_class($versionable), $versionable->getId());

                return $this->versionBuilder->buildVersion($versionable, $user, $previousVersion, $this->context);
            }
        } else {
            $username  = $user ? $user->getUsername() : self::DEFAULT_SYSTEM_USER;
            $className = \Doctrine\Common\Util\ClassUtils::getRealClass(get_class($versionable));

            return new Pending($className, $versionable->getId(), $username);
        }
    }

    /**
     * @return VersionRepository
     */
    public function getVersionRepository()
    {
        return $this->registry->getRepository('PimVersioningBundle:Version');
    }

    /**
     * Return product logs
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
     * Get the current user
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->user) {
            return $this->user;
        }

        return $this->registry->getRepository('OroUserBundle:User')
            ->findOneBy(['username' => self::DEFAULT_SYSTEM_USER]);
    }
}
