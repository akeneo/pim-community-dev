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

namespace Akeneo\AssetManager\Infrastructure\Validation\Attribute\MediaLink;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class Prefix extends Constraint
{
    public const MESSAGE_NOT_EMPTY_STRING = 'pim_asset_manager.attribute.validation.prefix.should_not_be_empty';
    public const PROTOCOL_NOT_ALLOWED = 'pim_asset_manager.attribute.validation.prefix.protocol_not_allowed';

    public function validatedBy()
    {
        return 'akeneo_assetmanager.validator.attribute.media_link.prefix_validator';
    }
}
