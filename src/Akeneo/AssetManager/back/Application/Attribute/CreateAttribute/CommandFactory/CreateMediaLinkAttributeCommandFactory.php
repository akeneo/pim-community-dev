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

namespace Akeneo\AssetManager\Application\Attribute\CreateAttribute\CommandFactory;

use Akeneo\AssetManager\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateMediaLinkAttributeCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class CreateMediaLinkAttributeCommandFactory extends AbstractCreateAttributeCommandFactory
{
    public function supports(array $normalizedCommand): bool
    {
        return isset($normalizedCommand['type']) && MediaLinkAttribute::ATTRIBUTE_TYPE === $normalizedCommand['type'];
    }

    public function create(array $normalizedCommand): AbstractCreateAttributeCommand
    {
        $this->checkCommonProperties($normalizedCommand);
        $normalizedCommand['media_type'] = isset($normalizedCommand['media_type']) ? $normalizedCommand['media_type'] : MediaType::IMAGE;
        $this->checkAdditionalProperties($normalizedCommand);

        return new CreateMediaLinkAttributeCommand(
            $normalizedCommand['asset_family_identifier'],
            $normalizedCommand['code'],
            $normalizedCommand['labels'] ?? [],
            $normalizedCommand['is_required'] ?? false,
            $normalizedCommand['is_read_only'] ?? false,
            $normalizedCommand['value_per_channel'] ?? false,
            $normalizedCommand['value_per_locale'] ?? false,
            $normalizedCommand['media_type'],
            $this->stringOrNull($normalizedCommand, 'prefix'),
            $this->stringOrNull($normalizedCommand, 'suffix')
        );
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkAdditionalProperties(array $nomalizedCommand): void
    {
        $keysToCheck = [
            'media_type',
        ];

        foreach ($keysToCheck as $keyToCheck) {
            if (!array_key_exists($keyToCheck, $nomalizedCommand)) {
                throw new \InvalidArgumentException(
                    sprintf('Expects normalized command to have key "%s"', $keyToCheck)
                );
            }
        }
    }

    private function stringOrNull(array $normalizedCommand, string $key): ?string
    {
        return isset($normalizedCommand[$key]) && '' !== $normalizedCommand[$key]
            ? (string) $normalizedCommand[$key] : null;
    }
}
