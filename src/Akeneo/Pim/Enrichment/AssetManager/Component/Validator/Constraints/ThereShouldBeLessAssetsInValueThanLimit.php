<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint to ensure there is no more assets in the product value than the limit defined.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class ThereShouldBeLessAssetsInValueThanLimit extends Constraint
{
    public $message = 'pim_asset_manager.product_value.validation.asset_multiple_link.should_contain_less_than_limit';

    public function validatedBy(): string
    {
        return 'limited_assets_in_value';
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
