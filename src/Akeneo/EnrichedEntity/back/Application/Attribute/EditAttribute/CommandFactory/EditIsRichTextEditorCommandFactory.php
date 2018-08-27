<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditIsRichTextEditorCommandFactory implements EditAttributeCommandFactoryInterface
{
    public function supports(array $normalizedCommand): bool
    {
        return isset($normalizedCommand['is_rich_text_editor'])
            && isset($normalizedCommand['identifier']['identifier'])
            && isset($normalizedCommand['identifier']['enriched_entity_identifier']);
    }

    public function create(array $normalizedCommand): AbstractEditAttributeCommand
    {
        if (!$this->supports($normalizedCommand)) {
            throw new \RuntimeException('Impossible to create an edit is rich text editor command.');
        }

        $command = new EditIsRichTextEditorCommand();
        $command->identifier = $normalizedCommand['identifier'];
        $command->isRichTextEditor = $normalizedCommand['is_rich_text_editor'];

        return $command;
    }
}
