<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Attribute\EditAttribute;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater\AttributeUpdaterRegistryInterface;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditAttributeHandler
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var AttributeUpdaterRegistryInterface */
    private $AttributeUpdaterRegistry;

    public function __construct(
        AttributeUpdaterRegistryInterface $attributeUpdaterRegistry,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->AttributeUpdaterRegistry = $attributeUpdaterRegistry;
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
        $attributeIdentifier = AttributeIdentifier::fromString($command->identifier);

        return $this->attributeRepository->getByIdentifier($attributeIdentifier);
    }

    private function editAttribute(AbstractAttribute $attribute, EditAttributeCommand $command): AbstractAttribute
    {
        foreach ($command->editCommands as $editCommand) {
            $editAttributeUpdater = $this->AttributeUpdaterRegistry->getUpdater($attribute, $editCommand);
            $attribute = ($editAttributeUpdater)($attribute, $editCommand);
        }

        return $attribute;
    }
}
