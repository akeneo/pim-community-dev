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

namespace Akeneo\AssetManager\Domain\Query\Asset;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;

/**
 * Find labels for given assets
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
interface FindAssetLabelsByCodesInterface
{
    /**
     * Find assets by their $assetFamilyIdentifier and their $codes,
     * then returns their labels as LabelCollection indexed by their code:
     *
     * [
     *      'starck' => LabelCollection,
     *      'dyson' => LabelCollection,
     * ]
     */
    public function find(AssetFamilyIdentifier $assetFamilyIdentifier, array $assetCodes): array;
}
