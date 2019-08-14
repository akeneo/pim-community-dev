<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ProductLinkRules extends Constraint
{
    public function validatedBy()
    {
        return 'akeneo_assetmanager.validator.asset_family.product_link_rules.rule_engine_validator_acl';
    }
}
