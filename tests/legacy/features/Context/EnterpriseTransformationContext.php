<?php

namespace Context;

use Context\TransformationContext as BaseTransformationContext;
use PimEnterprise\Component\ProductAsset\Model\CategoryInterface;

/**
 * Context for data transformations
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseTransformationContext extends BaseTransformationContext
{
    /**
     * @param string $code
     *
     * @Transform /^asset category "([^"]*)"$/
     *
     * @return CategoryInterface
     */
    public function castAssetCategoryCodeToAssetCategory($code)
    {
        return $this->getFixturesContext()->getAssetCategory($code);
    }
}
