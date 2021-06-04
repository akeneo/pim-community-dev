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
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\ConnectorAttribute;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\ConnectorAttributeOption;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\FindConnectorAttributeOptionInterface;

class InMemoryFindConnectorAttributeOption implements FindConnectorAttributeOptionInterface
{
    /** @var ConnectorAttribute[] */
    private array $attributes;

    public function __construct()
    {
        $this->attributes = [];
    }

    public function save(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeCode $attributeCode,
        ConnectorAttribute $connectorAttribute
    ): void {
        $this->attributes[(string) $assetFamilyIdentifier][(string) $attributeCode] = $connectorAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function find(AssetFamilyIdentifier $assetFamilyIdentifier, AttributeCode $attributeCode, OptionCode $optionCode): ?ConnectorAttributeOption
    {
        $connectorAttribute = $this->attributes[(string) $assetFamilyIdentifier][(string) $attributeCode] ?? null;

        if (null === $connectorAttribute) {
            return null;
        }

        $options = $connectorAttribute->normalize()['options'];

        $matchingOption = current(array_filter($options, fn ($option) => $option['code'] === (string) $optionCode));


        if (!$matchingOption) {
            return null;
        }

        return new ConnectorAttributeOption(
            OptionCode::fromString($matchingOption['code']),
            LabelCollection::fromArray($matchingOption['labels'])
        );
    }
}
