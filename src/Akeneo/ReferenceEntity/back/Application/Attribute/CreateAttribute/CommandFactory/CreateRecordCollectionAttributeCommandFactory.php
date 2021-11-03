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

namespace Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CommandFactory;

use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateRecordCollectionAttributeCommand;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class CreateRecordCollectionAttributeCommandFactory extends AbstractCreateAttributeCommandFactory
{
    public function supports(array $normalizedCommand): bool
    {
        return isset($normalizedCommand['type']) && 'record_collection' === $normalizedCommand['type'];
    }

    public function create(array $normalizedCommand): AbstractCreateAttributeCommand
    {
        $this->checkCommonProperties($normalizedCommand);
        $this->checkAdditionalProperties($normalizedCommand);

        return new CreateRecordCollectionAttributeCommand(
            $normalizedCommand['reference_entity_identifier'],
            $normalizedCommand['code'],
            $normalizedCommand['labels'] ?? [],
            $normalizedCommand['is_required'] ?? false,
            $normalizedCommand['value_per_channel'],
            $normalizedCommand['value_per_locale'],
            $normalizedCommand['record_type'] ?? null
        );
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkAdditionalProperties(array $normalizedCommand): void
    {
        $keysToCheck = [
            'record_type',
        ];

        foreach ($keysToCheck as $keyToCheck) {
            if (!array_key_exists($keyToCheck, $normalizedCommand)) {
                throw new \InvalidArgumentException(
                    sprintf('Expects normalized command to have key "%s"', $keyToCheck)
                );
            }
        }
    }
}
