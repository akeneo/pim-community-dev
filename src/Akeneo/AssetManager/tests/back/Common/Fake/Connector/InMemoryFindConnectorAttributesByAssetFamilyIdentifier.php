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
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\ConnectorAttribute;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\FindConnectorAttributesByAssetFamilyIdentifierInterface;

class InMemoryFindConnectorAttributesByAssetFamilyIdentifier implements FindConnectorAttributesByAssetFamilyIdentifierInterface
{
    /** @var ConnectorAttribute[] */
    private array $attributes;

    public function __construct()
    {
        $this->attributes = [];
    }

    public function save(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        ConnectorAttribute $connectorAttribute
    ): void {
        $this->attributes[(string) $assetFamilyIdentifier][] = $connectorAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function find(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        return $this->attributes[(string) $assetFamilyIdentifier] ?? [];
    }
}
