<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Anais Baune Lemaire <anais.lemaire@akeneo.com>
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AssetsShouldBelongToAssetFamily extends Constraint
{
    public const ERROR_MESSAGE = 'pim_asset_manager.product_value.validation.assets_should_belong_to_asset_family';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return 'pim_enrichment_assets_should_belong_to_asset_family';
    }
}
