<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditIsTextAreaCommandFactory implements EditAttributeCommandFactoryInterface
{
    public function supports(array $normalizedCommand): bool
    {
        return isset($normalizedCommand['is_text_area'])
            && isset($normalizedCommand['identifier']['identifier'])
            && isset($normalizedCommand['identifier']['enriched_entity_identifier']);
    }

    public function create(array $normalizedCommand): AbstractEditAttributeCommand
    {
        if (!$this->supports($normalizedCommand)) {
            throw new \RuntimeException('Impossible to create an edit is text area property command.');
        }

        $command = new EditIsTextAreaCommand();
        $command->identifier = $normalizedCommand['identifier'];
        $command->isTextArea = $normalizedCommand['is_text_area'] ?? null;

        return $command;
    }
}
