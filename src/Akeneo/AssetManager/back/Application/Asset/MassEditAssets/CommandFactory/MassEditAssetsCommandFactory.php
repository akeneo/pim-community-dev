<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Asset\MassEditAssets\CommandFactory;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditValueCommandFactoryRegistryInterface;
use Akeneo\AssetManager\Application\Asset\MassEditAssets\MassEditAssetsCommand;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation\CheckIfTransformationTarget;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class MassEditAssetsCommandFactory
{
    private FindAttributesIndexedByIdentifierInterface $sqlFindAttributesIndexedByIdentifier;
    private EditValueCommandFactoryRegistryInterface $editValueCommandFactoryRegistry;
    private CheckIfTransformationTarget $checkIfTransformationTarget;

    public function __construct(
        EditValueCommandFactoryRegistryInterface $editValueCommandFactoryRegistry,
        FindAttributesIndexedByIdentifierInterface $sqlFindAttributesIndexedByIdentifier,
        CheckIfTransformationTarget $checkIfTransformationTarget
    ) {
        $this->sqlFindAttributesIndexedByIdentifier = $sqlFindAttributesIndexedByIdentifier;
        $this->editValueCommandFactoryRegistry = $editValueCommandFactoryRegistry;
        $this->checkIfTransformationTarget = $checkIfTransformationTarget;
    }

    public function create(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetQuery $query,
        array $normalizedUpdaters
    ): MassEditAssetsCommand {
        if (!$this->isValidUpdaters($normalizedUpdaters)) {
            throw new \InvalidArgumentException('Impossible to create a command of mass asset edition.');
        }

        $attributesIndexedByIdentifier = $this->sqlFindAttributesIndexedByIdentifier->find($assetFamilyIdentifier);

        $filteredUpdaters = array_filter($normalizedUpdaters, function ($normalizedUpdater) use ($attributesIndexedByIdentifier) {
            if (!$this->isAttributeExisting($normalizedUpdater, $attributesIndexedByIdentifier)) {
                return false;
            }

            $attribute = $attributesIndexedByIdentifier[$normalizedUpdater['attribute']];

            return !$this->isAttributeTransformationTarget($attribute, $normalizedUpdater);
        });

        $updaters = array_reduce($filteredUpdaters, function ($result, $normalizedUpdater) use ($attributesIndexedByIdentifier) {
            $attribute = $attributesIndexedByIdentifier[$normalizedUpdater['attribute']];
            $updaterId = $normalizedUpdater['id'];
            unset($normalizedUpdater['id']);

            $editValueCommand = $this->editValueCommandFactoryRegistry
                ->getFactory($attribute, $normalizedUpdater)
                ->create($attribute, $normalizedUpdater);

            $result[$updaterId] = $editValueCommand;

            return $result;
        }, []);

        return new MassEditAssetsCommand((string) $assetFamilyIdentifier, $query->normalize(), $updaters);
    }

    private function isAttributeExisting($normalizedValue, $attributesIndexedByIdentifier): bool
    {
        return array_key_exists($normalizedValue['attribute'], $attributesIndexedByIdentifier);
    }

    private function isAttributeTransformationTarget(AbstractAttribute $attribute, array $normalizedValue): bool
    {
        return $this->checkIfTransformationTarget->forAttribute(
            $attribute,
            $normalizedValue['locale'] ?? null,
            $normalizedValue['channel'] ?? null
        );
    }

    private function isValidUpdaters(array $normalizedUpdaters): bool
    {
        foreach ($normalizedUpdaters as $normalizedUpdater) {
            if (
                !is_array($normalizedUpdater)
                || !array_key_exists('attribute', $normalizedUpdater)
                || !array_key_exists('channel', $normalizedUpdater)
                || !array_key_exists('locale', $normalizedUpdater)
                || !array_key_exists('data', $normalizedUpdater)
                || !array_key_exists('action', $normalizedUpdater)
                || !array_key_exists('id', $normalizedUpdater)
            ) {
                return false;
            }
        }

        return true;
    }
}
