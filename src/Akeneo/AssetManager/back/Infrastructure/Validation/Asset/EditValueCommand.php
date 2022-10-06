<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\Asset;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditValueCommand extends Constraint
{
    public const CHANNEL_IS_EXPECTED = 'pim_asset_manager.asset.validation.channel.is_expected';
    public const CHANNEL_IS_NOT_EXPECTED = 'pim_asset_manager.asset.validation.channel.is_not_expected';
    public const CHANNEL_SHOULD_EXIST = 'pim_asset_manager.asset.validation.channel.should_exist';

    public const LOCALE_IS_EXPECTED = 'pim_asset_manager.asset.validation.locale.is_expected';
    public const LOCALE_IS_NOT_EXPECTED = 'pim_asset_manager.asset.validation.locale.is_not_expected';
    public const LOCALE_IS_NOT_ACTIVATED = 'pim_asset_manager.asset.validation.locale.is_not_activated';
    public const LOCALE_IS_NOT_ACTIVATED_FOR_CHANNEL = 'pim_asset_manager.asset.validation.locale.is_not_activated_for_channel';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
