<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Connector;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\AbstractEditValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditValueCommandFactoryRegistryInterface;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\EditAssetValueCommandsFactory;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Exception\InvalidNamingConventionSourceAttributeType;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Exception\NamingConventionException;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Exception\NamingConventionPatternNotMatch;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConventionInterface;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class EditAssetCommandFactory
{
    private AssetFamilyRepositoryInterface $assetFamilyRepository;
    private AttributeRepositoryInterface $attributeRepository;
    private EditAssetValueCommandsFactory $editAssetValueCommandsFactory;
    private EditValueCommandFactoryRegistryInterface $editValueCommandFactoryRegistry;

    public function __construct(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AttributeRepositoryInterface $attributeRepository,
        EditAssetValueCommandsFactory $editAssetValueCommandsFactory,
        EditValueCommandFactoryRegistryInterface $editValueCommandFactoryRegistry
    ) {
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->attributeRepository = $attributeRepository;
        $this->editAssetValueCommandsFactory = $editAssetValueCommandsFactory;
        $this->editValueCommandFactoryRegistry = $editValueCommandFactoryRegistry;
    }

    /**
     * @throws NamingConventionException
     */
    public function create(array $normalizedCommand, AssetFamilyIdentifier $assetFamilyIdentifier): EditAssetCommand
    {
        $namingConvention = $this->getNamingConvention($assetFamilyIdentifier);

        $editAssetValueCommands = [];
        if ($namingConvention instanceof NamingConvention) {
            try {
                $editAssetValueCommands = $this->extractAndBuildEditAssetValueCommands(
                    $assetFamilyIdentifier,
                    $namingConvention,
                    $normalizedCommand
                );
            } catch (AttributeNotFoundException | NamingConventionPatternNotMatch | InvalidNamingConventionSourceAttributeType $e) {
                throw new NamingConventionException($e, $namingConvention->abortAssetCreationOnError());
            }
        }

        return new EditAssetCommand(
            $assetFamilyIdentifier->__toString(),
            $normalizedCommand['code'],
            $editAssetValueCommands
        );
    }

    /**
     * @return AbstractEditValueCommand[]
     * @throws NamingConventionPatternNotMatch
     * @throws InvalidNamingConventionSourceAttributeType
     * @throws AttributeNotFoundException
     */
    private function extractAndBuildEditAssetValueCommands(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        NamingConvention $namingConvention,
        array $normalizedCommand
    ): array {
        $stringDataValue = $namingConvention->getSource()->isAssetCode()
            ? $normalizedCommand['code']
            : $this->extractStringSource(
                $assetFamilyIdentifier,
                $namingConvention->getSource()->getProperty(),
                $namingConvention->getSource()->getChannelReference()->normalize(),
                $namingConvention->getSource()->getLocaleReference()->normalize(),
                $normalizedCommand['values']
            );

        if (null === $stringDataValue) {
            return [];
        }

        return $this->editAssetValueCommandsFactory->create(
            $assetFamilyIdentifier,
            $namingConvention,
            $stringDataValue
        );
    }

    private function getNamingConvention(AssetFamilyIdentifier $assetFamilyIdentifier): ?NamingConventionInterface
    {
        try {
            $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        } catch (AssetFamilyNotFoundException $e) {
            return null;
        }

        return $assetFamily->getNamingConvention();
    }

    /**
     * @throws AttributeNotFoundException
     */
    private function extractStringSource(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        string $sourceAttributeCode,
        ?string $channel,
        ?string $locale,
        array $normalizedValues
    ): ?string {
        $sourceAttribute = $this->attributeRepository->getByCodeAndAssetFamilyIdentifier(
            AttributeCode::fromString($sourceAttributeCode),
            $assetFamilyIdentifier
        );

        if (!array_key_exists($sourceAttributeCode, $normalizedValues)) {
            return null;
        }

        foreach ($normalizedValues[$sourceAttributeCode] as $normalizedValue) {
            if ($normalizedValue['channel'] === $channel && $normalizedValue['locale'] === $locale) {
                if ($sourceAttribute instanceof MediaFileAttribute) {
                    $editValueCommandFactory = $this->editValueCommandFactoryRegistry->getFactory($sourceAttribute, $normalizedValue);
                    $editAssetValueCommand = $editValueCommandFactory->create($sourceAttribute, $normalizedValue);

                    return $editAssetValueCommand->originalFilename;
                } elseif (
                    $sourceAttribute instanceof MediaLinkAttribute
                    || $sourceAttribute instanceof TextAttribute
                ) {
                    return $normalizedValue['data'];
                }

                throw new InvalidNamingConventionSourceAttributeType();
            }
        }

        return null;
    }
}
