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

namespace Akeneo\AssetManager\Domain\Query\Asset;

/**
 * Find labels for given asset identifiers
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
interface FindAssetLabelsByIdentifiersInterface
{
    /**
     * Find assets by their $assetIdentifiers then returns their labels by their asset identifier:
     * [
     *      'designer_starck_abcdef123456789' => [
     *          'labels' => [
     *              'fr_FR' => 'Un label',
     *              'en_US' => 'A label'
     *          ],
     *          'code' => 'starck'
     *      ],
     *      'designer_dyson_abcdef123456789' => [
     *          'labels' => [
     *              'fr_FR' => 'Un label',
     *              'en_US' => 'A label'
     *          ],
     *          'code' => 'dyson'
     *      ],
     * ]
     */
    public function find(array $assetIdentifiers): array;
}
