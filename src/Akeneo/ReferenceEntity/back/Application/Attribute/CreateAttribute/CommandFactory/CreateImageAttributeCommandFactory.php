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
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateImageAttributeCommand;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CreateImageAttributeCommandFactory extends AbstractCreateAttributeCommandFactory
{
    public function supports(array $normalizedCommand): bool
    {
        return isset($normalizedCommand['type']) && 'image' === $normalizedCommand['type'];
    }

    public function create(array $normalizedCommand): AbstractCreateAttributeCommand
    {
        $command = new CreateImageAttributeCommand();
        $this->fillCommonProperties($command, $normalizedCommand);

        $this->checkAdditionalProperties($normalizedCommand);

        $command->maxFileSize = (string) $normalizedCommand['max_file_size'];
        $command->allowedExtensions = $normalizedCommand['allowed_extensions'];

        return $command;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkAdditionalProperties(array $nomalizedCommand): void
    {
        $keysToCheck = [
            'max_file_size',
            'allowed_extensions',
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
