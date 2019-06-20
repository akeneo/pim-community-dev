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
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CreateImageAttributeCommandFactory extends AbstractCreateAttributeCommandFactory
{
    private const NO_LIMIT = null;

    public function supports(array $normalizedCommand): bool
    {
        return isset($normalizedCommand['type']) && 'image' === $normalizedCommand['type'];
    }

    public function create(array $normalizedCommand): AbstractCreateAttributeCommand
    {
        $this->checkCommonProperties($normalizedCommand);

        $maxFileSize = isset($normalizedCommand['max_file_size']) ?
            (string)$normalizedCommand['max_file_size'] : self::NO_LIMIT;
        $allowedExtensions = isset($normalizedCommand['allowed_extensions']) ?
            $normalizedCommand['allowed_extensions'] : AttributeAllowedExtensions::ALL_ALLOWED;
        $command = new CreateImageAttributeCommand(
            $normalizedCommand['reference_entity_identifier'],
            $normalizedCommand['code'],
            $normalizedCommand['labels'] ?? [],
            $normalizedCommand['is_required'] ?? false,
            $normalizedCommand['value_per_channel'],
            $normalizedCommand['value_per_locale'],
            $maxFileSize,
            $allowedExtensions
        );

        return $command;
    }
}
