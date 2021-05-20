<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditAttributeCommandFactory implements EditAttributeCommandFactoryInterface
{
    private EditAttributeCommandFactoryRegistryInterface $editAttributeCommandFactoryRegistry;

    public function __construct(EditAttributeCommandFactoryRegistryInterface $editAttributeCommandFactoryRegistry)
    {
        $this->editAttributeCommandFactoryRegistry = $editAttributeCommandFactoryRegistry;
    }

    public function supports(array $normalizedCommand): bool
    {
        return array_key_exists('identifier', $normalizedCommand);
    }

    public function create(array $normalizedCommand): AbstractEditAttributeCommand
    {
        if (!$this->supports($normalizedCommand)) {
            throw new \RuntimeException('Impossible to create a command of attribute edition.');
        }

        $editCommands = [];
        foreach ($this->editAttributeCommandFactoryRegistry->getFactories($normalizedCommand) as $editCommandFactory) {
            $editCommands[] = $editCommandFactory->create($normalizedCommand);
        }

        return new EditAttributeCommand(
            $normalizedCommand['identifier'],
            $editCommands
        );
    }
}
