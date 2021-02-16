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

namespace Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation\CheckIfTransformationTarget;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditAssetCommandFactory
{
    /** @var FindAttributesIndexedByIdentifierInterface */
    private $sqlFindAttributesIndexedByIdentifier;

    /** @var EditValueCommandFactoryRegistryInterface */
    private $editValueCommandFactoryRegistry;

    /** @var CheckIfTransformationTarget */
    private $checkIfTransformationTarget;

    public function __construct(
        EditValueCommandFactoryRegistryInterface $editValueCommandFactoryRegistry,
        FindAttributesIndexedByIdentifierInterface $sqlFindAttributesIndexedByIdentifier,
        CheckIfTransformationTarget $checkIfTransformationTarget
    ) {
        $this->sqlFindAttributesIndexedByIdentifier = $sqlFindAttributesIndexedByIdentifier;
        $this->editValueCommandFactoryRegistry = $editValueCommandFactoryRegistry;
        $this->checkIfTransformationTarget = $checkIfTransformationTarget;
    }

    public function create(array $normalizedCommand): EditAssetCommand
    {
        if (!$this->isValid($normalizedCommand)) {
            throw new \RuntimeException('Impossible to create a command of asset edition.');
        }

        $command = new EditAssetCommand(
            $normalizedCommand['asset_family_identifier'],
            $normalizedCommand['code'],
            []
        );

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($command->assetFamilyIdentifier);
        $attributesIndexedByIdentifier = $this->sqlFindAttributesIndexedByIdentifier->find($assetFamilyIdentifier);

        foreach ($normalizedCommand['values'] as $normalizedValue) {
            if (!$this->isUserInputCorrectlyFormed($normalizedValue)) {
                // we ignore the user input, it might be malformed.
                continue;
            }
            if (!$this->isAttributeExisting($normalizedValue, $attributesIndexedByIdentifier)) {
                // Attribute might has been removed
                continue;
            }

            $attribute = $attributesIndexedByIdentifier[$normalizedValue['attribute']];
            if ($this->isAttributeTargetOrATransformation($attribute, $normalizedValue)) {
                // Target attributes can not be updated through this action (read only)
                continue;
            }

            $command->editAssetValueCommands[] = $this->editValueCommandFactoryRegistry
                ->getFactory($attribute, $normalizedValue)
                ->create($attribute, $normalizedValue);
        }

        return $command;
    }

    private function isValid(array $normalizedCommand): bool
    {
        return array_key_exists('asset_family_identifier', $normalizedCommand)
            && array_key_exists('code', $normalizedCommand)
            && array_key_exists('values', $normalizedCommand);
    }

    private function isUserInputCorrectlyFormed($normalizedValue): bool
    {
        return array_key_exists('attribute', $normalizedValue);
    }

    private function isAttributeExisting($normalizedValue, $attributesIndexedByIdentifier): bool
    {
        return array_key_exists($normalizedValue['attribute'], $attributesIndexedByIdentifier);
    }

    private function isAttributeTargetOrATransformation(AbstractAttribute $attribute, array $normalizedValue)
    {
        return $this->checkIfTransformationTarget->forAttribute(
            $attribute,
            $normalizedValue['locale'] ?? null,
            $normalizedValue['channel'] ?? null
        );
    }
}
