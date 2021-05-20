<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditAttributeCommand extends AbstractEditAttributeCommand
{
    /** @var AbstractEditAttributeCommand[] */
    public array $editCommands = [];

    public function __construct(string $identifier, array $editCommands)
    {
        parent::__construct($identifier);

        $this->editCommands = $editCommands;
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
