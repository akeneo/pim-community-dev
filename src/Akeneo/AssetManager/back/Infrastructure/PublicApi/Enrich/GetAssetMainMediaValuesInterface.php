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

namespace Akeneo\AssetManager\Infrastructure\PublicApi\Enrich;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
interface GetAssetMainMediaValuesInterface
{
    /**
     * @return array    A list of main media values by asset codes. For example:
     *
     * {
     *      "asset_code1": [
     *          {
     *              "data": "http://...",
     *              "locale": "en_US",
     *              "channel": null,
     *              "attribute": "attribute_identifier",
     *          },
     *          {
     *              "data": "http://...",
     *              "locale": "fr_FR",
     *              "channel": null,
     *              "attribute": "attribute_identifier",
     *          }
     *      ],
     * }
     */
    public function forAssetFamilyAndAssetCodes(string $assetFamilyIdentifier, array $assetCodes): array;
}
