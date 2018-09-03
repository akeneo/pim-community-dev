<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser;

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
    /** @staticvar string */
    const ACTION_UPDATE_ENTITY = 'update_entity';

    /** @staticvar string */
    const ACTION_UPDATE_COLLECTION = 'update_collection';

    /** @staticvar string */
    const ACTION_DELETE = 'delete';

    /**
     * Check if the guesser support the action
     *
     * @param string $action
     *
     * @return bool
     */
    public function supportAction($action);

    /**
     * Get updated entities
     *
     * @param EntityManager $em
     * @param object        $entity
     * @param string        $action
     *
     * @return array
     */
    public function guessUpdates(EntityManager $em, $entity, $action);
}
