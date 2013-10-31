<?php

namespace Pim\Bundle\VersioningBundle\UpdateGuesser;

use Doctrine\ORM\EntityManager;

/**
 * Update guesser interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface UpdateGuesserInterface
{
    /**
     * Get updated entities
     *
     * @param EntityManager $em
     * @param object        $entity
     *
     * @return array
     */
    public function guessUpdates(EntityManager $em, $entity);
}
