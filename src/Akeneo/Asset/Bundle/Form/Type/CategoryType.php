<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\Form\Type;

use Akeneo\Pim\Enrichment\Bundle\Form\Type\CategoryType as BaseCategoryType;

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
    public function getBlockPrefix()
    {
        return 'pimee_asset_category';
    }
}
