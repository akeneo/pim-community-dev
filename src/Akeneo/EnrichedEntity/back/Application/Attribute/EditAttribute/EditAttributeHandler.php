<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\EditAttribute;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\AttributeUpdater\AttributeUpdaterRegistryInterface;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditAttributeHandler
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var AttributeUpdaterRegistryInterface */
    private $editAttributeAdapterRegistry;

    public function __construct(
        AttributeUpdaterRegistryInterface $editAttributeAdapterRegistry,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->editAttributeAdapterRegistry = $editAttributeAdapterRegistry;
        $this->attributeRepository = $attributeRepository;
    }

    public function __invoke(EditAttributeCommand $editCommand): void
    {
        $attribute = $this->getAttribute($editCommand);
        $attribute = $this->editAttribute($attribute, $editCommand);

        $this->attributeRepository->update($attribute);
    }

    private function getAttribute(EditAttributeCommand $command): AbstractAttribute
    {
        $attributeIdentifier = AttributeIdentifier::create(
            $command->identifier['enriched_entity_identifier'],
            $command->identifier['identifier']
        );

        return $this->attributeRepository->getByIdentifier($attributeIdentifier);
    }

    private function editAttribute($attribute, EditAttributeCommand $command): AbstractAttribute
    {
        foreach ($command->editCommands as $editCommand) {
            $editAttribute = $this->editAttributeAdapterRegistry->getUpdater($editCommand);
            $attribute = ($editAttribute)($attribute, $editCommand);
        }

        return $attribute;
    }
}
