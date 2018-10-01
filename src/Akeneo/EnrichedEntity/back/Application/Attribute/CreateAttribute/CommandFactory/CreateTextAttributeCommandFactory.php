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
        $command = new CreateTextAttributeCommand();
        $this->fillCommonProperties($command, $normalizedCommand);
        $command->maxLength = $normalizedCommand['max_length'] ?? AttributeMaxLength::NO_LIMIT;
        $command->isTextarea = $normalizedCommand['is_textarea'] ?? false;
        $command->isRichTextEditor = $normalizedCommand['is_textarea'] ?? false;
        $command->validationRule = $normalizedCommand['validation_rule'] ?? AttributeValidationRule::NONE;
        $command->regularExpression = $normalizedCommand['regular_expression'] ?? null;

        return $command;
    }
}
