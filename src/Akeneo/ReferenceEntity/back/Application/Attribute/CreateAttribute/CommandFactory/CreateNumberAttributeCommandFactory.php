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

namespace Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CommandFactory;

use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateNumberAttributeCommand;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class CreateNumberAttributeCommandFactory extends AbstractCreateAttributeCommandFactory
{
    public function supports(array $normalizedCommand): bool
    {
        return isset($normalizedCommand['type']) && 'number' === $normalizedCommand['type'];
    }

    public function create(array $normalizedCommand): AbstractCreateAttributeCommand
    {
        $this->checkCommonProperties($normalizedCommand);

        $command = new CreateNumberAttributeCommand(
            $normalizedCommand['reference_entity_identifier'],
            $normalizedCommand['code'],
            $normalizedCommand['labels'] ?? [],
            $normalizedCommand['is_required'] ?? false,
            $normalizedCommand['value_per_channel'],
            $normalizedCommand['value_per_locale'],
            $normalizedCommand['is_decimal'] ?? false,
            $normalizedCommand['min_value'] ?? null,
            $normalizedCommand['max_value'] ?? null
        );

        return $command;
    }
}
