<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditIsRichTextEditorCommandFactory implements EditAttributeCommandFactoryInterface
{
    public function supports(array $normalizedCommand): bool
    {
        return array_key_exists('is_rich_text_editor', $normalizedCommand)
            && array_key_exists('identifier', $normalizedCommand);
    }

    public function create(array $normalizedCommand): AbstractEditAttributeCommand
    {
        if (!$this->supports($normalizedCommand)) {
            throw new \RuntimeException('Impossible to create an edit is rich text editor command.');
        }

        return new EditIsRichTextEditorCommand(
            $normalizedCommand['identifier'],
            $normalizedCommand['is_rich_text_editor']
        );
    }
}
