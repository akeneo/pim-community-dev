<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CommandFactory;

use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateOptionAttributeCommand;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CreateOptionAttributeCommandFactory extends AbstractCreateAttributeCommandFactory
{
    public function supports(array $normalizedCommand): bool
    {
        return isset($normalizedCommand['type']) && 'option' === $normalizedCommand['type'];
    }

    public function create(array $normalizedCommand): AbstractCreateAttributeCommand
    {
        $this->checkCommonProperties($normalizedCommand);

        $command = new CreateOptionAttributeCommand(
            $normalizedCommand['reference_entity_identifier'],
            $normalizedCommand['code'],
            $normalizedCommand['labels'] ?? [],
            $normalizedCommand['is_required'] ?? false,
            $normalizedCommand['value_per_channel'],
            $normalizedCommand['value_per_locale']
        );

        return $command;
    }
}
