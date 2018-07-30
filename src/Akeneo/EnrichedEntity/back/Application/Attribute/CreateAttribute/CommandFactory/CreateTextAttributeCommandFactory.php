<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CommandFactory;

use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CreateTextAttributeCommandFactory implements CreateAttributeCommandFactoryInterface
{
    public function supports(array $normalizedCommand): bool
    {
        return isset($normalizedCommand['type']) && 'text' === $normalizedCommand['type'];
    }

    public function create(array $normalizedCommand): AbstractCreateAttributeCommand
    {
        $command = new CreateTextAttributeCommand();
        $command->identifier = [
            'identifier' => $normalizedCommand['identifier']['identifier'] ?? null,
            'enriched_entity_identifier' => $normalizedCommand['identifier']['enriched_entity_identifier'] ?? null
        ];
        $command->code = $normalizedCommand['code'] ?? null;
        $command->enrichedEntityIdentifier = $normalizedCommand['enriched_entity_identifier'] ?? null;
        $command->labels = $normalizedCommand['labels'] ?? null;
        $command->order = $normalizedCommand['order'] ?? null;
        $command->required = $normalizedCommand['required'] ?? null;
        $command->valuePerChannel = $normalizedCommand['value_per_channel'] ?? null;
        $command->valuePerLocale = $normalizedCommand['value_per_locale'] ?? null;
        $command->maxLength = $normalizedCommand['max_length'] ?? null;

        return $command;
    }
}
