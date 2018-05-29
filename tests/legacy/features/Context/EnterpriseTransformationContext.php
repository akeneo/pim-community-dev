<?php

namespace Context;

use Akeneo\Asset\Component\Model\CategoryInterface;
use Context\TransformationContext as BaseTransformationContext;

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
