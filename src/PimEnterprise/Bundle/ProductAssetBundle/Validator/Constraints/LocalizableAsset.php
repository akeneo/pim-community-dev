<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for localizable asset
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class LocalizableAsset extends Constraint
{
    /** @var string */
    public $expectedLocaleMessage = 'All references of an asset that contains several references must be localized.';

    /** @var string */
    public $unexpectedLocaleMessage = 'The unique reference of an asset can not be localized.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pimee_product_localizable_asset_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
