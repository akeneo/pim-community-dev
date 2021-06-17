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

namespace Akeneo\AssetManager\Common\Fake\Connector;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Domain\Query\Asset\Connector\ConnectorAsset;
use Akeneo\AssetManager\Domain\Query\Asset\Connector\FindConnectorAssetsByIdentifiersInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindConnectorAssetsByIdentifiers implements FindConnectorAssetsByIdentifiersInterface
{
    /** @var ConnectorAsset[] */
    private array $assetsByIdentifier;

    public function __construct()
    {
        $this->assetsByIdentifier = [];
    }

    public function save(AssetIdentifier $assetIdentifier, ConnectorAsset $connectorAsset): void
    {
        $this->assetsByIdentifier[(string) $assetIdentifier] = $connectorAsset;
    }

    /**
     * {@inheritdoc}
     */
    public function find(array $identifiers, AssetQuery $assetQuery): array
    {
        $assets = [];

        foreach ($identifiers as $identifier) {
            if (isset($this->assetsByIdentifier[$identifier])) {
                $assets[] = $this->filterAssetValues($this->assetsByIdentifier[$identifier], $assetQuery);
            }
        }

        return $assets;
    }

    private function filterAssetValues(ConnectorAsset $connectorAsset, AssetQuery $assetQuery): ConnectorAsset
    {
        $channelReference = $assetQuery->getChannelReferenceValuesFilter();
        if (!$channelReference->isEmpty()) {
            $connectorAsset = $connectorAsset->getAssetWithValuesFilteredOnChannel($channelReference->getIdentifier());
        }

        $localesIdentifiers = $assetQuery->getLocaleIdentifiersValuesFilter();
        if (!$localesIdentifiers->isEmpty()) {
            $connectorAsset = $connectorAsset->getAssetWithValuesFilteredOnLocales($localesIdentifiers);
        }

        return $connectorAsset;
    }
}
