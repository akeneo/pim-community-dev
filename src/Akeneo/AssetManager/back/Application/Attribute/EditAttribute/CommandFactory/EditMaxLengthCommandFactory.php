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

namespace Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditMaxLengthCommandFactory implements EditAttributeCommandFactoryInterface
{
    public function supports(array $normalizedCommand): bool
    {
        return array_key_exists('max_length', $normalizedCommand)
            && array_key_exists('identifier', $normalizedCommand);
    }

    public function create(array $normalizedCommand): AbstractEditAttributeCommand
    {
        if (!$this->supports($normalizedCommand)) {
            throw new \RuntimeException('Impossible to create an edit max length property command.');
        }

        return new EditMaxLengthCommand(
            $normalizedCommand['identifier'],
            $normalizedCommand['max_length']
        );
    }
}
