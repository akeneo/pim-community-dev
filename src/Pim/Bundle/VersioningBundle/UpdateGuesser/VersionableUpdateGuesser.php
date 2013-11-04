<?php

namespace Pim\Bundle\VersioningBundle\UpdateGuesser;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\VersioningBundle\Entity\VersionableInterface;

/**
 * Fields update guesser
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionableUpdateGuesser implements UpdateGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function guessUpdates(Entitymanager $em, $entity)
    {
        $pendings = array();
        if ($entity instanceof VersionableInterface) {
            $pendings[]= $entity;
        }

        return $pendings;
    }
}
