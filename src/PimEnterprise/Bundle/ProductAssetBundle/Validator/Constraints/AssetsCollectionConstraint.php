<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for assets collection attribute
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class AssetsCollectionConstraint extends Constraint
{
    /** @var string */
    public $message = 'The assets collection attribute can not be scopable nether localizable';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pimee_product_asset_attribute_assets_collection_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
