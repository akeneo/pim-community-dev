<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\MediaLink;

use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\AbstractEditAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommandFactoryInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class EditPrefixCommandFactory implements EditAttributeCommandFactoryInterface
{
    public function supports(array $normalizedCommand): bool
    {
        return array_key_exists('prefix', $normalizedCommand)
            && array_key_exists('identifier', $normalizedCommand);
    }

    public function create(array $normalizedCommand): AbstractEditAttributeCommand
    {
        if (!$this->supports($normalizedCommand)) {
            throw new \RuntimeException('Impossible to create an edit "prefix" property command.');
        }

        return new EditPrefixCommand(
            $normalizedCommand['identifier'],
            $normalizedCommand['prefix']
        );
    }
}
