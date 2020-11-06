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
     */
    public function supportAction(string $action): bool;

    /**
     * Get updated entities
     *
     * @param EntityManager $em
     * @param object        $entity
     * @param string        $action
     */
    public function guessUpdates(EntityManager $em, object $entity, string $action): array;
}
