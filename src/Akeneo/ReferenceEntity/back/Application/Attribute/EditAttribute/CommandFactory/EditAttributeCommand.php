<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditAttributeCommand extends AbstractEditAttributeCommand
{
    /**
     * @param AbstractEditAttributeCommand[] $editCommands
     */
    public function __construct(
        string $identifier,
        public array $editCommands
    ) {
        parent::__construct($identifier);
    }

    public function findCommand(string $className): ?AbstractEditAttributeCommand
    {
        foreach ($this->editCommands as $command) {
            if ($command instanceof $className) {
                return $command;
            }
        }

        return null;
    }
}
