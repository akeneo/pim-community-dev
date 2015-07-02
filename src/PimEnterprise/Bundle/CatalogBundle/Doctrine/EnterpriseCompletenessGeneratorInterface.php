<?php

namespace PimEnterprise\Bundle\CatalogBundle\Doctrine;

use Pim\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;

interface EnterpriseCompletenessGeneratorInterface extends CompletenessGeneratorInterface
{
    /**
     * Schedule recalculation of completenesses for all products linked to an asset
     *
     * @param AssetInterface $asset
     */
    public function scheduleForAsset(AssetInterface $asset);
}
