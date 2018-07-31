<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CommandFactory;

use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CreateTextAttributeCommandFactory extends AbstractCreateAttributeCommandFactory
{
    public function supports(array $normalizedCommand): bool
    {
        return isset($normalizedCommand['type']) && 'text' === $normalizedCommand['type'];
    }

    public function create(array $normalizedCommand): AbstractCreateAttributeCommand
    {
        $command = new CreateTextAttributeCommand();
        $this->fillCommonProperties($command, $normalizedCommand);
        $command->maxLength = $normalizedCommand['max_length'] ?? null;

        return $command;
    }
}
