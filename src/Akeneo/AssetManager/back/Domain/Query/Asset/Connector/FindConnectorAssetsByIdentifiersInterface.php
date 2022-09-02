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

namespace Akeneo\AssetManager\Domain\Query\Asset\Connector;

use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;

/**
 * Find connector assets by identifiers.
 * The asset values will be filtered by the filters defined in the search query.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
interface FindConnectorAssetsByIdentifiersInterface
{
    /**
     * @param string[]    $identifiers
     *
     * @return ConnectorAsset[]
     */
    public function find(array $identifiers, AssetQuery $assetQuery): array;
}
