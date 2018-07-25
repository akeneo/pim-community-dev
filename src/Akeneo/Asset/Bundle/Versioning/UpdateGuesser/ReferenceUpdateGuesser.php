<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\Versioning\UpdateGuesser;

use Akeneo\Asset\Component\Model\ReferenceInterface;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Doctrine\ORM\EntityManager;

/**
 * Reference update guesser
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ReferenceUpdateGuesser implements UpdateGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportAction($action)
    {
        return $action === UpdateGuesserInterface::ACTION_UPDATE_ENTITY;
    }

    /**
     * {@inheritdoc}
     */
    public function guessUpdates(EntityManager $em, $entity, $action)
    {
        $pendings = [];

        if ($entity instanceof ReferenceInterface) {
            $pendings[] = $entity->getAsset();
        }

        return $pendings;
    }
}
