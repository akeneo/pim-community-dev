<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditAttributeCommand extends AbstractEditAttributeCommand
{
    /** @var AbstractEditAttributeCommand[] */
    public $editCommands;

    public function getCommand(string $className): ?AbstractEditAttributeCommand
    {
        foreach ($this->editCommands as $command) {
            if ($command instanceof $className) {
                return $command;
            }
        }

        return null;
    }
}
