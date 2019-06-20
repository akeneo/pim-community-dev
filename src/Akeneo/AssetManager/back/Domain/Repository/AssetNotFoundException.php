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

namespace Akeneo\AssetManager\Domain\Repository;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetNotFoundException extends \RuntimeException
{
    public static function withIdentifier(AssetIdentifier $identifier): self
    {
        $message = sprintf(
            'Could not find asset with identifier "%s"',
            (string) $identifier
        );

        return new self($message);
    }

    public static function withAssetFamilyAndCode(AssetFamilyIdentifier $assetFamilyIdentifier, AssetCode $code): self
    {
        $message = sprintf(
            'Could not find asset with code "%s" for asset family "%s"',
            (string) $code,
            (string) $assetFamilyIdentifier
        );

        return new self($message);
    }
}
