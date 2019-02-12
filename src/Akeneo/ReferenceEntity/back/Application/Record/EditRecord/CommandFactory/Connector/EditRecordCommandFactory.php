<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\Connector;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditValueCommandFactoryRegistryInterface;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditRecordCommandFactory
{
    /** @var FindAttributesIndexedByIdentifierInterface */
    private $findAttributesIndexedByIdentifier;

    /** @var EditValueCommandFactoryRegistryInterface */
    private $editValueCommandFactoryRegistry;

    public function __construct(
        EditValueCommandFactoryRegistryInterface $editValueCommandFactoryRegistry,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier
    ) {
        $this->editValueCommandFactoryRegistry = $editValueCommandFactoryRegistry;
        $this->findAttributesIndexedByIdentifier = $findAttributesIndexedByIdentifier;
    }

    public function create(ReferenceEntityIdentifier $referenceEntityIdentifier, array $normalizedRecord): EditRecordCommand
    {
        $command = new EditRecordCommand(
            $referenceEntityIdentifier->normalize(),
            $normalizedRecord['code'],
            [],
            null,
            $this->createEditRecordValueCommands($referenceEntityIdentifier, $normalizedRecord)
        );

        return $command;
    }

    private function createEditRecordValueCommands(ReferenceEntityIdentifier $referenceEntityIdentifier, array $normalizedRecord): array
    {
        if (empty($normalizedRecord['values'])) {
            return [];
        }

        $attributesIndexedByCodes = $this->getAttributesIndexedByCodes($referenceEntityIdentifier);
        $editRecordValueCommands = [];

        foreach ($normalizedRecord['values'] as $attributeCode => $normalizedValues) {
            $this->assertAttributeExists((string) $attributeCode, $attributesIndexedByCodes);
            $attribute = $attributesIndexedByCodes[$attributeCode];

            foreach ($normalizedValues as $normalizedValue) {
                $editValueCommandFactory = $this->editValueCommandFactoryRegistry->getFactory($attribute, $normalizedValue);
                $editRecordValueCommands[] = $editValueCommandFactory->create($attribute, $normalizedValue);
            }
        }

        return $editRecordValueCommands;
    }

    private function getAttributesIndexedByCodes(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        $attributesIndexedByIdentifier = ($this->findAttributesIndexedByIdentifier)($referenceEntityIdentifier);

        $attributesIndexedByCodes = [];
        foreach ($attributesIndexedByIdentifier as $attribute) {
            $attributesIndexedByCodes[(string) $attribute->getCode()] = $attribute;
        }

        return $attributesIndexedByCodes;
    }

    private function assertAttributeExists(string $attributeCode, array $existingAttributes): void
    {
        if (!array_key_exists($attributeCode, $existingAttributes)) {
            throw new \InvalidArgumentException(sprintf(
                'Attribute "%s" does not exist for this reference entity', $attributeCode
            ));
        }
    }
}
