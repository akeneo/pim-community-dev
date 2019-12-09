<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Validation\Attribute;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class AllowedExtensions extends Constraint
{
    public const MESSAGE_CANNOT_CONTAIN_EXTENSION_SEPARATOR = 'pim_asset_manager.attribute.validation.media_file.cannot_contain_extension_separator';
    public const MESSAGE_SHOULD_ONLY_CONTAIN_LOWERCASE_LETTERS_AND_NUMBERS = 'pim_asset_manager.attribute.validation.media_file.should_only_contain_lowercase_letters_and_numbers';
    public const MESSAGE_CANNOT_BE_LONGER_THAN_MAX = 'pim_asset_manager.attribute.validation.media_file.cannot_be_longer_than_max';
    public const MESSAGE_THERE_CANNOT_BE_DUPLICATE_EXTENSIONS = 'pim_asset_manager.attribute.validation.media_file.there_cannot_be_duplicated_extensions';
}
