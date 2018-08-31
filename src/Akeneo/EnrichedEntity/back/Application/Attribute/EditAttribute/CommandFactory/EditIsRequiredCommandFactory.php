<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditIsRequiredCommandFactory implements EditAttributeCommandFactoryInterface
{
    public function supports(array $normalizedCommand): bool
    {
        return array_key_exists('is_required', $normalizedCommand)
            && array_key_exists('identifier', $normalizedCommand);
    }

    public function create(array $normalizedCommand): AbstractEditAttributeCommand
    {
        if (!$this->supports($normalizedCommand)) {
            throw new \RuntimeException('Impossible to create an edit required property command.');
        }

        $command = new EditIsRequiredCommand();
        $command->identifier = $normalizedCommand['identifier'];
        $command->isRequired = $normalizedCommand['is_required'];

        return $command;
    }
}
