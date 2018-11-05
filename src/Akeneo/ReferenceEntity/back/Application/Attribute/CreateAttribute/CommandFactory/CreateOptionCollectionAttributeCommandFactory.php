<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CommandFactory;

use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateOptionCollectionAttributeCommand;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CreateOptionCollectionAttributeCommandFactory extends AbstractCreateAttributeCommandFactory
{
    public function supports(array $normalizedCommand): bool
    {
        return isset($normalizedCommand['type']) && 'option_collection' === $normalizedCommand['type'];
    }

    public function create(array $normalizedCommand): AbstractCreateAttributeCommand
    {
        $command = new CreateOptionCollectionAttributeCommand();
        $this->fillCommonProperties($command, $normalizedCommand);

        return $command;
    }
}
