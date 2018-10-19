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

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
abstract class AbstractCreateAttributeCommandFactory implements CreateAttributeCommandFactoryInterface
{
    protected function fillCommonProperties(
        AbstractCreateAttributeCommand $command,
        array $normalizedCommand
    ): AbstractCreateAttributeCommand {
        $this->checkCommonProperties($normalizedCommand);

        $command->code = $normalizedCommand['code'];
        $command->referenceEntityIdentifier = $normalizedCommand['reference_entity_identifier'];
        $command->labels = $normalizedCommand['labels'];
        $command->order = $normalizedCommand['order'];
        $command->isRequired = $normalizedCommand['is_required'];
        $command->valuePerChannel = $normalizedCommand['value_per_channel'];
        $command->valuePerLocale = $normalizedCommand['value_per_locale'];

        return $command;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkCommonProperties(array $nomalizedCommand): void
    {
        $keysToCheck = [
            'code',
            'reference_entity_identifier',
            'labels',
            'order',
            'is_required',
            'value_per_channel',
            'value_per_locale',
        ];

        foreach ($keysToCheck as $keyToCheck) {
            if (!key_exists($keyToCheck, $nomalizedCommand)) {
                throw new \InvalidArgumentException(
                    sprintf('Expects normalized command to have key "%s"', $keyToCheck)
                );
            }
        }
    }
}
