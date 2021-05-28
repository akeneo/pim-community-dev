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
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateNumberAttributeCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\NumberAttribute;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class CreateNumberAttributeCommandFactory extends AbstractCreateAttributeCommandFactory
{
    public function supports(array $normalizedCommand): bool
    {
        return isset($normalizedCommand['type']) && NumberAttribute::ATTRIBUTE_TYPE === $normalizedCommand['type'];
    }

    public function create(array $normalizedCommand): AbstractCreateAttributeCommand
    {
        $this->checkCommonProperties($normalizedCommand);

        return new CreateNumberAttributeCommand(
            $normalizedCommand['asset_family_identifier'],
            $normalizedCommand['code'],
            $normalizedCommand['labels'] ?? [],
            $normalizedCommand['is_required'] ?? false,
            $normalizedCommand['is_read_only'] ?? false,
            $normalizedCommand['value_per_channel'] ?? false,
            $normalizedCommand['value_per_locale'] ?? false,
            $normalizedCommand['decimals_allowed'] ?? false,
            $this->stringOrNull($normalizedCommand, 'min_value'),
            $this->stringOrNull($normalizedCommand, 'max_value')
        );
    }

    private function stringOrNull(array $normalizedCommand, string $key)
    {
        return isset($normalizedCommand[$key]) && '' !== $normalizedCommand[$key]
            ? (string) $normalizedCommand[$key] : null;
    }
}
