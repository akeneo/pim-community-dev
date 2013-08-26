<?php

namespace Pim\Bundle\VersioningBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\VersioningBundle\Entity\VersionableInterface;
use Pim\Bundle\VersioningBundle\Entity\Pending;

/**
 * Pending repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PendingRepository extends EntityRepository
{
    /**
     * Return the pending version for the versionable entity
     *
     * @return Pending | null
     */
    public function getPending(VersionableInterface $versionable)
    {
        $criteria = array(
            'resourceName' => get_class($versionable),
            'resourceId'   => $versionable->getId(),
            'status'       => Pending::STATUS_PENDING
        );
        $pending = $this->findOneBy($criteria);

        return $pending;
    }

    /**
     * Return the pending versions
     *
     * @return array
     */
    public function getPendings(VersionableInterface $versionable)
    {
        $criteria = array(
            'resourceName' => get_class($versionable),
            'resourceId'   => $versionable->getId(),
            'status'       => Pending::STATUS_PENDING
        );

        return $this->findBy($criteria);
    }
}
