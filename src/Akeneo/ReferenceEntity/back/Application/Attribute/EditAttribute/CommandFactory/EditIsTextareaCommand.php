<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditIsTextareaCommand extends AbstractEditAttributeCommand
{
    public function __construct(
        string $identifier,
        public ?bool $isTextarea
    ) {
        parent::__construct($identifier);
    }
}
