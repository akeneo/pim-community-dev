<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditAllowedExtensionsCommandFactory implements EditAttributeCommandFactoryInterface
{
    public function supports(array $normalizedCommand): bool
    {
        return isset($normalizedCommand['allowed_extensions'])
            && isset($normalizedCommand['identifier']['identifier'])
            && isset($normalizedCommand['identifier']['enriched_entity_identifier']);
    }

    public function create(array $normalizedCommand): AbstractEditAttributeCommand
    {
        if(!$this->supports($normalizedCommand)) {
            throw new \RuntimeException('Impossible to create an edit allowed extensions property command.');
        }
        $command = new EditAllowedExtensionsCommand();
        $command->identifier = $normalizedCommand['identifier']; // Could be extracted in AbstractEditAttributeCommandFactory
        $command->allowedExtensions = $normalizedCommand['allowed_extensions'];

        return $command;
    }
}
