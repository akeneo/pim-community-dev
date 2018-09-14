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

namespace Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Attribute\SqlFindAttributesIndexedByIdentifier;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditRecordCommandFactory
{
    /** @var EditRecordValueCommandFactoryRegistryInterface  */
    private $editRecordValueCommandFactoryRegistry;

    /** @var SqlFindAttributesIndexedByIdentifier  */
    private $sqlFindAttributesIndexedByIdentifier;

    public function __construct(
        EditRecordValueCommandFactoryRegistryInterface $editRecordValueCommandFactoryRegistry,
        SqlFindAttributesIndexedByIdentifier $sqlFindAttributesIndexedByIdentifier
    ) {
        $this->editRecordValueCommandFactoryRegistry = $editRecordValueCommandFactoryRegistry;
        $this->sqlFindAttributesIndexedByIdentifier = $sqlFindAttributesIndexedByIdentifier;
    }

    public function supports(array $normalizedCommand): bool
    {
        return array_key_exists('enriched_entity_identifier', $normalizedCommand)
            && array_key_exists('code', $normalizedCommand)
            && array_key_exists('labels', $normalizedCommand)
            && array_key_exists('values', $normalizedCommand);
    }

    public function create(array $normalizedCommand): EditRecordCommand
    {
        if (!$this->supports($normalizedCommand)) {
            throw new \RuntimeException('Impossible to create a command of record edition.');
        }

        $command = new EditRecordCommand();
        $command->enrichedEntityIdentifier = $normalizedCommand['enriched_entity_identifier'] ?? null;
        $command->code = $normalizedCommand['code'] ?? null;
        $command->labels = $normalizedCommand['labels'] ?? [];
        $command->editRecordValueCommands = [];

        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString($command->enrichedEntityIdentifier);
        $attributesIndexedByIdentifier = ($this->sqlFindAttributesIndexedByIdentifier)($enrichedEntityIdentifier);

        foreach ($normalizedCommand['values'] as $normalizedValue) {
            if (!key_exists('attribute', $normalizedValue)) {
                throw new \RuntimeException('Missing key "attribute" to create the edit record value command.');
            }

            $attribute = $attributesIndexedByIdentifier[$normalizedValue['attribute']];
            $editRecordValueCommandFactory = $this->editRecordValueCommandFactoryRegistry->getFactory($attribute);
            $command->editRecordValueCommands[] = $editRecordValueCommandFactory->create($normalizedValue, $attribute);
        }


        return $command;
    }
}
