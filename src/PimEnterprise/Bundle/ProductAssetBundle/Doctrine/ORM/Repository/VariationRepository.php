<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Doctrine\ORM\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use PimEnterprise\Component\ProductAsset\Repository\VariationRepositoryInterface;

/**
 * Implementation of VariationRepositoryInterface
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class VariationRepository extends EntityRepository implements VariationRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findNotGenerated()
    {
        $qb = $this->createQueryBuilder('v')
            ->where('v.fileInfo IS NULL and v.sourceFileInfo IS NOT NULL');

        return $qb->getQuery()->getResult();
    }
}
