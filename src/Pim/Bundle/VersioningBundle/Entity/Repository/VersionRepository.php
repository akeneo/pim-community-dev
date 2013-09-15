<?php

namespace Pim\Bundle\VersioningBundle\Entity\Repository;

use Pim\Bundle\CatalogBundle\Doctrine\EntityRepository;
use Pim\Bundle\VersioningBundle\Entity\Version;

/**
 * Version repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionRepository extends EntityRepository
{
    /**
     * @return Version|null
     */
    public function findPreviousVersion(Version $current)
    {
        $previous = $this
            ->findOneBy(
                array('resourceId' => $current->getResourceId(), 'resourceName' => $current->getResourceName()),
                array('loggedAt' => 'desc')
            );

        return $previous;
    }
}
