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

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyQuery;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorAssetFamily;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\FindConnectorAssetFamilyItemsInterface;

class InMemoryFindConnectorAssetFamilyItems implements FindConnectorAssetFamilyItemsInterface
{
    /** @var ConnectorAssetFamily[] */
    private array $results;

    public function __construct()
    {
        $this->results = [];
    }

    public function save(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        ConnectorAssetFamily $connectorAssetFamily
    ): void {
        $this->results[(string) $assetFamilyIdentifier] = $connectorAssetFamily;
    }

    /**
     * {@inheritdoc}
     */
    public function find(AssetFamilyQuery $query): array
    {
        $searchAfterCode = $query->getSearchAfterIdentifier();
        $assetFamilies = array_values(array_filter($this->results, fn (ConnectorAssetFamily $assetFamily): bool => null === $searchAfterCode
            || strcasecmp((string) $assetFamily->getIdentifier(), $searchAfterCode) > 0));

        usort($assetFamilies, fn (ConnectorAssetFamily $first, ConnectorAssetFamily $second) => strcasecmp((string) $first->getIdentifier(), (string) $second->getIdentifier()));

        return array_slice($assetFamilies, 0, $query->getSize());
    }
}
