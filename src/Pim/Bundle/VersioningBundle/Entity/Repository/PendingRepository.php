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
     * Return a query builder for activated currencies
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
}
