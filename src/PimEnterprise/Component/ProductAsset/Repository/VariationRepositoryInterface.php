<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectRepository;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;

/**
 * Product asset variation repository interface
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
interface VariationRepositoryInterface extends ObjectRepository
{
    /**
     * @return VariationInterface[]|ArrayCollection
     */
    public function findNotGenerated();
}
