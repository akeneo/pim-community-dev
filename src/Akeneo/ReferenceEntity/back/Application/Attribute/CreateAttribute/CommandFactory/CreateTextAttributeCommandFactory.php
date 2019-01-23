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
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CreateTextAttributeCommandFactory extends AbstractCreateAttributeCommandFactory
{
    public function supports(array $normalizedCommand): bool
    {
        return isset($normalizedCommand['type']) && 'text' === $normalizedCommand['type'];
    }

    public function create(array $normalizedCommand): AbstractCreateAttributeCommand
    {
        $this->checkCommonProperties($normalizedCommand);

        $command = new CreateTextAttributeCommand(
            $normalizedCommand['reference_entity_identifier'],
            $normalizedCommand['code'],
            $normalizedCommand['labels'] ?? [],
            $normalizedCommand['is_required'] ?? false,
            $normalizedCommand['value_per_channel'],
            $normalizedCommand['value_per_locale'],
            $normalizedCommand['max_length'] ?? AttributeMaxLength::NO_LIMIT,
            $normalizedCommand['is_textarea'] ?? false,
            $normalizedCommand['is_textarea'] ?? false,
            $normalizedCommand['validation_rule'] ?? AttributeValidationRule::NONE,
            $normalizedCommand['regular_expression'] ?? null
        );

        return $command;
    }
}
