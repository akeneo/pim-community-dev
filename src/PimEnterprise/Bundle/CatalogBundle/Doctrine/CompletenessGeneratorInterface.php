<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\Doctrine;

use Pim\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface as BaseCompletenessGeneratorInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
interface CompletenessGeneratorInterface extends BaseCompletenessGeneratorInterface
{
    /**
     * Schedule recalculation of completenesses for all products linked to an asset
     *
     * @param AssetInterface $asset
     */
    public function scheduleForAsset(AssetInterface $asset);
}
