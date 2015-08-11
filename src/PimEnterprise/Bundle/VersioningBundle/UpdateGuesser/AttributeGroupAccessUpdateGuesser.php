<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\VersioningBundle\UpdateGuesser;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use PimEnterprise\Bundle\SecurityBundle\Model\AttributeGroupAccessInterface;

/**
 * AttributeGroup access update guesser
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class AttributeGroupAccessUpdateGuesser implements UpdateGuesserInterface
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

        if ($entity instanceof AttributeGroupAccessInterface) {
            $pendings[] = $entity->getAttributeGroup();
        }

        return $pendings;
    }
}
