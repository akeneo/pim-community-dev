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

namespace Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\Connector;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditValueCommandFactoryRegistryInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditAssetCommandFactory
{
    private FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier;

    private EditValueCommandFactoryRegistryInterface $editValueCommandFactoryRegistry;

    public function __construct(
        EditValueCommandFactoryRegistryInterface $editValueCommandFactoryRegistry,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier
    ) {
        $this->editValueCommandFactoryRegistry = $editValueCommandFactoryRegistry;
        $this->findAttributesIndexedByIdentifier = $findAttributesIndexedByIdentifier;
    }

    public function create(AssetFamilyIdentifier $assetFamilyIdentifier, array $normalizedAsset): EditAssetCommand
    {
        return new EditAssetCommand(
            $assetFamilyIdentifier->normalize(),
            $normalizedAsset['code'],
            $this->createEditAssetValueCommands($assetFamilyIdentifier, $normalizedAsset)
        );
    }

    private function createEditAssetValueCommands(AssetFamilyIdentifier $assetFamilyIdentifier, array $normalizedAsset): array
    {
        if (empty($normalizedAsset['values'])) {
            return [];
        }

        $attributesIndexedByCodes = $this->getAttributesIndexedByCodes($assetFamilyIdentifier);
        $editAssetValueCommands = [];

        foreach ($normalizedAsset['values'] as $attributeCode => $normalizedValues) {
            $this->assertAttributeExists((string) $attributeCode, $attributesIndexedByCodes);
            $attribute = $attributesIndexedByCodes[$attributeCode];

            foreach ($normalizedValues as $normalizedValue) {
                $editValueCommandFactory = $this->editValueCommandFactoryRegistry->getFactory($attribute, $normalizedValue);
                $editAssetValueCommands[] = $editValueCommandFactory->create($attribute, $normalizedValue);
            }
        }

        return $editAssetValueCommands;
    }

    private function getAttributesIndexedByCodes(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        $attributesIndexedByIdentifier = $this->findAttributesIndexedByIdentifier->find($assetFamilyIdentifier);

        $attributesIndexedByCodes = [];
        foreach ($attributesIndexedByIdentifier as $attribute) {
            $attributesIndexedByCodes[(string) $attribute->getCode()] = $attribute;
        }

        return $attributesIndexedByCodes;
    }

    private function assertAttributeExists(string $attributeCode, array $existingAttributes): void
    {
        if (!array_key_exists($attributeCode, $existingAttributes)) {
            throw new \InvalidArgumentException(sprintf(
                'Attribute "%s" does not exist for this asset family', $attributeCode
            ));
        }
    }
}
