<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class ExecuteAssetFamilyNamingConventionCommand
{
    public string $assetFamilyIdentifier;

    public function __construct(string $assetFamilyIdentifier)
    {
        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
    }
}
