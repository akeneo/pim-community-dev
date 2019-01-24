<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditIsRichTextEditorCommand extends AbstractEditAttributeCommand
{
    /** @var bool */
    public $isRichTextEditor;

    public function __construct(string $identifier, ?bool $isRichTextEditor)
    {
        parent::__construct($identifier);

        $this->isRichTextEditor = $isRichTextEditor;
    }
}
