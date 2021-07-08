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
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
interface FindAssetLabelTranslationInterface
{
    /**
     * @return array An array of asset labels indexed by asset code. For example:
     *
     * [
     *     'packshot1' => 'Packshot 1',
     *     'packshot2' => 'Packshot 2'
     * ],
     */
    public function byFamilyCodeAndAssetCodes(string $familyCode, array $assetCodes, $locale): array;
}
