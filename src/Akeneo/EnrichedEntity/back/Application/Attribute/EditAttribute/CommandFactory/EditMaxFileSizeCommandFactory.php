<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditMaxFileSizeCommandFactory implements EditAttributeCommandFactoryInterface
{
    public function supports(array $normalizedCommand): bool
    {
        return isset($normalizedCommand['max_file_size'])
            && isset($normalizedCommand['identifier']['identifier'])
            && isset($normalizedCommand['identifier']['enriched_entity_identifier']);
    }

    public function create(array $normalizedCommand): AbstractEditAttributeCommand
    {
        if (!$this->supports($normalizedCommand)) {
            throw new \RuntimeException('Impossible to create an edit max file size property command.');
        }
        $command = new EditMaxFileSizeCommand();
        $command->identifier = $normalizedCommand['identifier'];
        $command->maxFileSize = $normalizedCommand['max_file_size'] ?? null;

        return $command;
    }
}
