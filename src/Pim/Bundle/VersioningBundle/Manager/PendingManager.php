<?php

namespace Pim\Bundle\VersioningBundle\Manager;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\VersioningBundle\Entity\Pending;

/**
 * Pending manager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PendingManager
{
    /**
     * @var ObjectManager
     */
    protected $em;

    /**
     * Constructor
     *
     * @param ObjectManager $em
     */
    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    /**
     * Return the pending version for the versionable entity
     * @param object $versionable
     *
     * @return Pending | null
     */
    public function getPendingVersion($versionable)
    {
        $criteria = [
            'resourceName' => get_class($versionable),
            'resourceId'   => $versionable->getId()
        ];
        $pending = $this->getRepository()->findOneBy($criteria);

        return $pending;
    }

    /**
     * Return the pending versions for the versionable entity
     * @param object $versionable
     *
     * @return Pending[]
     */
    public function getPendingVersions($versionable)
    {
        $criteria = [
            'resourceName' => get_class($versionable),
            'resourceId'   => $versionable->getId()
        ];
        $pendings = $this->getRepository()->findBy($criteria);

        return $pendings;
    }

    /**
     * Get pending versions
     *
     * @return Pending[]
     */
    public function getAllPendingVersions()
    {
        $versions = $this->getRepository()->findAll();

        return $versions;
    }

    /**
     * Return versionable entity from pending
     *
     * @param Pending $pending
     *
     * @return object
     */
    public function getRelatedVersionable(Pending $pending)
    {
        $repo = $this->em->getRepository($pending->getResourceName());
        $versionable = $repo->find($pending->getResourceId());

        return $versionable;
    }

    /**
     * @return EntityRepository
     */
    protected function getRepository()
    {
        return $this->em->getRepository('PimVersioningBundle:Pending');
    }
}
