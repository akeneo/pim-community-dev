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

namespace Akeneo\AssetManager\Application\Asset\SearchAsset;

use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Domain\Query\Asset\Connector\ConnectorAssetResult;
use Akeneo\AssetManager\Domain\Query\Asset\Connector\FindConnectorAssetsByIdentifiersInterface;
use Akeneo\AssetManager\Domain\Query\Asset\FindIdentifiersForQueryInterface;

/**
 * This service takes a asset search query and will return a list of connector-assets.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SearchConnectorAsset
{
    /** @var FindIdentifiersForQueryInterface */
    private $findIdentifiersForQuery;

    /** @var FindConnectorAssetsByIdentifiersInterface */
    private $findConnectorAssetsByIdentifiers;

    public function __construct(
        FindIdentifiersForQueryInterface $findIdentifiersForQuery,
        FindConnectorAssetsByIdentifiersInterface $findConnectorAssetsByIdentifiers
    ) {
        $this->findIdentifiersForQuery = $findIdentifiersForQuery;
        $this->findConnectorAssetsByIdentifiers = $findConnectorAssetsByIdentifiers;
    }

    public function __invoke(AssetQuery $query): ConnectorAssetResult
    {
        $result = $this->findIdentifiersForQuery->find($query);
        $assets = empty($result) ? [] : $this->findConnectorAssetsByIdentifiers->find($result->identifiers, $query);

        return ConnectorAssetResult::createFromSearchAfterQuery($assets, $result->lastSortValue);
    }
}
