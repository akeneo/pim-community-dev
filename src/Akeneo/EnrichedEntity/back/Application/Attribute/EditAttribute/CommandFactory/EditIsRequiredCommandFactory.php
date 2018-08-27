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
        return isset($normalizedCommand['is_required'])
            && isset($normalizedCommand['identifier']['identifier'])
            && isset($normalizedCommand['identifier']['enriched_entity_identifier']);
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
