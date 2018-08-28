<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditAttributeCommandFactory implements EditAttributeCommandFactoryInterface
{
    /** @var EditAttributeCommandFactoryRegistryInterface */
    private $editAttributeCommandFactoryRegistry;

    public function __construct(EditAttributeCommandFactoryRegistryInterface $editAttributeCommandFactoryRegistry)
    {
        $this->editAttributeCommandFactoryRegistry = $editAttributeCommandFactoryRegistry;
    }

    public function supports(array $normalizedCommand): bool
    {
        return isset($normalizedCommand['identifier']['identifier'])
            && isset($normalizedCommand['identifier']['enriched_entity_identifier']);
    }

    public function create(array $normalizedCommand): AbstractEditAttributeCommand
    {
        if (!$this->supports($normalizedCommand)) {
            throw new \RuntimeException('Impossible to create a command of attribute edition.');
        }

        $command = new EditAttributeCommand();
        $command->identifier = $normalizedCommand['identifier'];
        $command->editCommands = [];
        foreach ($this->editAttributeCommandFactoryRegistry->getFactories($normalizedCommand) as $editCommandFactory) {
            $command->editCommands[] = $editCommandFactory->create($normalizedCommand);
        }

        return $command;
    }
}
