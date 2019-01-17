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

namespace Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditRecordCommandFactory
{
    /** @var FindAttributesIndexedByIdentifierInterface */
    private $sqlFindAttributesIndexedByIdentifier;

    /** @var EditValueCommandFactoryRegistryInterface */
    private $editValueCommandFactoryRegistry;

    public function __construct(
        EditValueCommandFactoryRegistryInterface $editValueCommandFactoryRegistry,
        FindAttributesIndexedByIdentifierInterface $sqlFindAttributesIndexedByIdentifier
    ) {
        $this->sqlFindAttributesIndexedByIdentifier = $sqlFindAttributesIndexedByIdentifier;
        $this->editValueCommandFactoryRegistry = $editValueCommandFactoryRegistry;
    }

    public function create(array $normalizedCommand): EditRecordCommand
    {
        if (!$this->isValid($normalizedCommand)) {
            throw new \RuntimeException('Impossible to create a command of record edition.');
        }

        $command = new EditRecordCommand();
        $command->referenceEntityIdentifier = $normalizedCommand['reference_entity_identifier'] ?? null;
        $command->code = $normalizedCommand['code'] ?? null;
        $command->editRecordValueCommands = [];

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($command->referenceEntityIdentifier);
        $attributesIndexedByIdentifier = ($this->sqlFindAttributesIndexedByIdentifier)($referenceEntityIdentifier);

        foreach ($normalizedCommand['values'] as $normalizedValue) {
            if (!$this->isUserIntputCorrectlyFormed($normalizedValue)) {
                // we ignore the user input, it might be malformed.
                continue;
            }
            if (!$this->isAttributeExisting($normalizedValue, $attributesIndexedByIdentifier)) {
                // Attribute might has been removed
                continue;
            }

            $attribute = $attributesIndexedByIdentifier[$normalizedValue['attribute']];
            $command->editRecordValueCommands[] = $this->editValueCommandFactoryRegistry
                ->getFactory($attribute, $normalizedValue)
                ->create($attribute, $normalizedValue);
        }

        return $command;
    }

    private function isValid(array $normalizedCommand): bool
    {
        return array_key_exists('reference_entity_identifier', $normalizedCommand)
            && array_key_exists('code', $normalizedCommand)
            && array_key_exists('values', $normalizedCommand);
    }

    private function isUserIntputCorrectlyFormed($normalizedValue): bool
    {
        return array_key_exists('attribute', $normalizedValue);
    }

    private function isAttributeExisting($normalizedValue, $attributesIndexedByIdentifier): bool
    {
        return array_key_exists($normalizedValue['attribute'], $attributesIndexedByIdentifier);
    }
}
