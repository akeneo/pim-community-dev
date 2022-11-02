<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Valentin Dijkstra <valentin.dijkstra@akeneo.com>
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * @deprecated Please use Akeneo\Pim\Enrichment\AssetManager\Component\Validator\Constraints\AssetsShouldBelongToAssetFamily instead
 * @todo Merge master/5.0 remove this class
 */
class AssetShouldExist extends Constraint
{
    public const ERROR_MESSAGE = 'pim_asset_manager.product_value.validation.asset_should_exist';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return 'pim_enrichment_asset_should_exist';
    }
}
