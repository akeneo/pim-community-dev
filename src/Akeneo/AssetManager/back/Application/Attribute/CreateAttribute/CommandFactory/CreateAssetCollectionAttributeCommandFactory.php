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

namespace Akeneo\AssetManager\Application\Attribute\CreateAttribute\CommandFactory;

use Akeneo\AssetManager\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateAssetCollectionAttributeCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\AssetCollectionAttribute;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class CreateAssetCollectionAttributeCommandFactory extends AbstractCreateAttributeCommandFactory
{
    public function supports(array $normalizedCommand): bool
    {
        return isset($normalizedCommand['type']) && AssetCollectionAttribute::ATTRIBUTE_TYPE === $normalizedCommand['type'];
    }

    public function create(array $normalizedCommand): AbstractCreateAttributeCommand
    {
        $this->checkCommonProperties($normalizedCommand);
        $this->checkAdditionalProperties($normalizedCommand);

        $command = new CreateAssetCollectionAttributeCommand(
            $normalizedCommand['asset_family_identifier'],
            $normalizedCommand['code'],
            $normalizedCommand['labels'] ?? [],
            $normalizedCommand['is_required'] ?? false,
            $normalizedCommand['value_per_channel'],
            $normalizedCommand['value_per_locale'],
            $normalizedCommand['asset_type']
        );

        return $command;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkAdditionalProperties(array $nomalizedCommand): void
    {
        $keysToCheck = [
            'asset_type',
        ];

        foreach ($keysToCheck as $keyToCheck) {
            if (!key_exists($keyToCheck, $nomalizedCommand)) {
                throw new \InvalidArgumentException(
                    sprintf('Expects normalized command to have key "%s"', $keyToCheck)
                );
            }
        }
    }
}
