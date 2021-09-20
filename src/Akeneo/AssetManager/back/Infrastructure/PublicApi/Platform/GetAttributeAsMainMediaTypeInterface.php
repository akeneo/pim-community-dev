<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\PublicApi\Platform;

interface GetAttributeAsMainMediaTypeInterface
{
    /**
     * @return bool    It checks if the main media of asset family is a media file
     */
    public function isMediaFile(string $assetFamilyIdentifier): bool;

    /**
     * @return bool It checks if the main media of asset family is a media link
     */
    public function isMediaLink(string $assetFamilyIdentifier): bool;
}
