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
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorAssetFamily;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\FindConnectorAssetFamilyByAssetFamilyIdentifierInterface;

class InMemoryFindConnectorAssetFamilyByAssetFamilyIdentifier implements FindConnectorAssetFamilyByAssetFamilyIdentifierInterface
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
    public function find(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        bool $caseSensitive = true
    ): ?ConnectorAssetFamily {
        if ($caseSensitive) {
            return $this->results[(string) $assetFamilyIdentifier] ?? null;
        }

        foreach ($this->results as $identifier => $assetFamily) {
            if (strtolower($identifier) === strtolower((string) $assetFamilyIdentifier)) {
                return $assetFamily;
            }
        }

        return null;
    }
}
