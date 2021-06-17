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

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\Connector\ConnectorAsset;
use Akeneo\AssetManager\Domain\Query\Asset\Connector\FindConnectorAssetByAssetFamilyAndCodeInterface;

/**
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindConnectorAssetByAssetFamilyAndCode implements FindConnectorAssetByAssetFamilyAndCodeInterface
{
    /** @var ConnectorAsset[] */
    private array $results;

    public function __construct()
    {
        $this->results = [];
    }

    public function save(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetCode $assetCode,
        ConnectorAsset $connectorAsset
    ): void {
        $this->results[sprintf('%s____%s', $assetFamilyIdentifier, $assetCode)] = $connectorAsset;
    }

    /**
     * {@inheritdoc}
     */
    public function find(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetCode $assetCode
    ): ?ConnectorAsset {
        return $this->results[sprintf('%s____%s', $assetFamilyIdentifier, $assetCode)] ?? null;
    }
}
