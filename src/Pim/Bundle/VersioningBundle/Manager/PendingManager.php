<?php

namespace Pim\Bundle\VersioningBundle\Manager;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\VersioningBundle\Entity\Pending;
use Pim\Bundle\VersioningBundle\Entity\VersionableInterface;

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
     * @var VersionManager
     */
    protected $versionManager;

    /**
     * @var AuditManager
     */
    protected $auditManager;

    /**
     * Constructor
     *
     * @param VersionManager $vm
     * @param AuditManager   $am
     * @param ObjectManager  $em
     */
    public function __construct(VersionManager $versionM, AuditManager $auditM, ObjectManager $em)
    {
        $this->em             = $em;
        $this->versionManager = $versionM;
        $this->auditManager   = $auditM;
    }

    /**
     * Create Version and Audit from Pending
     *
     * @param Pending $pending
     */
    public function createVersionAndAudit(Pending $pending, $withFlush = true)
    {
        $user = $this->em->getRepository('OroUserBundle:User')->findOneBy(array('username' => $pending->getUsername()));
        $versionable = $this->getRelatedVersionable($pending);

        $current = $this->versionManager->buildVersion($versionable, $user);
        $this->em->persist($current);
        foreach ($this->getPendingVersions($versionable) as $pending) {
            $this->em->remove($pending);
        }

        $previous = $this->versionManager->getPreviousVersion($current);
        $audit = $this->auditManager->buildAudit($current, $previous);
        $diffData = $audit->getData();
        if (!empty($diffData)) {
            $this->em->persist($audit);
        }

        if ($withFlush) {
            $this->em->flush();
        }
    }

    /**
     * Return the pending version for the versionable entity
     *
     * @return Pending | null
     */
    public function getPendingVersion(VersionableInterface $versionable)
    {
        $criteria = array(
            'resourceName' => get_class($versionable),
            'resourceId'   => $versionable->getId()
        );
        $pending = $this->getRepository()->findOneBy($criteria);

        return $pending;
    }

    /**
     * Return the pending versions for the versionable entity
     *
     * @return Pending[]
     */
    public function getPendingVersions(VersionableInterface $versionable)
    {
        $criteria = array(
            'resourceName' => get_class($versionable),
            'resourceId'   => $versionable->getId()
        );
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
     * @return VersionableInterface;
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
