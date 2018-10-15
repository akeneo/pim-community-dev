<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\UpdateGuesser;

use Akeneo\Pim\Permission\Component\Model\CategoryAccessInterface;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Doctrine\ORM\EntityManager;

/**
 * Category access update guesser
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class CategoryAccessUpdateGuesser implements UpdateGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportAction($action)
    {
        return $action === UpdateGuesserInterface::ACTION_UPDATE_ENTITY ||
            $action === UpdateGuesserInterface::ACTION_DELETE;
    }

    /**
     * {@inheritdoc}
     */
    public function guessUpdates(EntityManager $em, $entity, $action)
    {
        $pendings = [];

        if ($entity instanceof CategoryAccessInterface) {
            $pendings[] = $entity->getCategory();
        }

        return $pendings;
    }
}
