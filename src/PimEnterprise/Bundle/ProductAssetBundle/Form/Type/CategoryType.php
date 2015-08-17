<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Form\Type;

use Pim\Bundle\EnrichBundle\Form\Type\CategoryType as BaseCategoryType;

/**
 * Type for an asset category form
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class CategoryType extends BaseCategoryType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pimee_asset_category';
    }
}
