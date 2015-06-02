<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;

/**
 * Product asset repository
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class AssetRepository extends EntityRepository implements AssetRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($reference)
    {
        return $this->findOneBy(['code' => $reference]);
    }
}
