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

namespace Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\AbstractEditValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditValueCommandFactoryRegistryInterface;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Exception\NamingConventionPatternNotMatch;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeNotFoundException;

/**
 * Allows to split the source value according to the naming convention business rules, and creates a list of
 * AbstractEditValueCommand with the result of the split.
 * We only check that the target attributes exist, Validation of the commands must be performed outside.
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class EditAssetValueCommandsFactory
{
    private FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier;

    private EditValueCommandFactoryRegistryInterface $editValueCommandFactoryRegistry;

    public function __construct(
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        EditValueCommandFactoryRegistryInterface $editValueCommandFactoryRegistry
    ) {
        $this->findAttributesIndexedByIdentifier = $findAttributesIndexedByIdentifier;
        $this->editValueCommandFactoryRegistry = $editValueCommandFactoryRegistry;
    }

    /**
     * @param AssetFamilyIdentifier $assetFamilyIdentifier
     * @param NamingConvention      $namingConvention
     * @param string                $sourceValue
     * @return AbstractEditValueCommand[]
     * @throws NamingConventionPatternNotMatch
     */
    public function create(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        NamingConvention $namingConvention,
        string $sourceValue
    ): array {
        $result = preg_match($namingConvention->getPattern()->normalize(), $sourceValue, $matches);
        if (false === $result || 0 === $result) {
            if (!$namingConvention->abortAssetCreationOnError()) {
                return [];
            }

            throw new NamingConventionPatternNotMatch('This value does not match the naming convention\'s pattern');
        }

        return $this->buildEditAssetValueCommands($namingConvention, $assetFamilyIdentifier, $matches);
    }

    /**
     * @param NamingConvention      $namingConvention
     * @param AssetFamilyIdentifier $assetFamilyIdentifier
     * @param array                 $matches
     * @return AbstractEditValueCommand[]
     */
    private function buildEditAssetValueCommands(
        NamingConvention $namingConvention,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        array $matches
    ): array {
        $attributesIndexedByCodes = $this->getAttributesIndexedByCodes($assetFamilyIdentifier);
        $editAssetValueCommands = [];

        $values = $this->removeNonStringKeysAndEmptyValues($matches);
        foreach ($values as $attributeCode => $data) {
            if (!array_key_exists($attributeCode, $attributesIndexedByCodes)) {
                if (!$namingConvention->abortAssetCreationOnError()) {
                    continue;
                }

                throw new AttributeNotFoundException(sprintf(
                    'Attribute "%s" does not exist for this asset family',
                    $attributeCode
                ));
            }

            $attribute = $attributesIndexedByCodes[$attributeCode];
            $normalizedValue = [
                'data' => $data,
                'channel' => null,
                'locale' => null,
            ];

            try {
                $editValueCommandFactory = $this->editValueCommandFactoryRegistry->getFactory($attribute, $normalizedValue);
                $editAssetValueCommands[] = $editValueCommandFactory->create($attribute, $normalizedValue);
            } catch (\RuntimeException $e) {
                if (!$namingConvention->abortAssetCreationOnError()) {
                    continue;
                }

                throw $e;
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

    private function removeNonStringKeysAndEmptyValues(array $matches): array
    {
        return array_filter($matches, fn($value, $key) => is_string($key) && '' !== $value, ARRAY_FILTER_USE_BOTH);
    }
}
