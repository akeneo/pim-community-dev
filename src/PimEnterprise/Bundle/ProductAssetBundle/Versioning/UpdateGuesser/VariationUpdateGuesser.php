<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Versioning\UpdateGuesser;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;

/**
 * Variation update guesser
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class VariationUpdateGuesser implements UpdateGuesserInterface
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

        if ($entity instanceof VariationInterface) {
            $pendings[] = $entity->getAsset();
        }

        return $pendings;
    }
}
