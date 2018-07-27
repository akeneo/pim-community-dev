<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CommandFactory;

use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CreateImageAttributeCommand;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CreateImageAttributeAttributeCommandFactory implements CreateAttributeCommandFactoryInterface
{
    public function supports(array $normalizedCommand): bool
    {
        return isset($normalizedCommand['type']) && 'image' === $normalizedCommand['type'];
    }

    public function create(array $normalizedCommand): AbstractCreateAttributeCommand
    {
        $command = new CreateImageAttributeCommand();
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
        $command->maxFileSize = $normalizedCommand['max_file_size'] ?? null;
        $command->allowedExtensions = $normalizedCommand['allowed_extensions'] ?? null;

        return $command;
    }
}
