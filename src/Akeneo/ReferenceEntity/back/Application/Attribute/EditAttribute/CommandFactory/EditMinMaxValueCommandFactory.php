<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditMinMaxValueCommandFactory implements EditAttributeCommandFactoryInterface
{
    public function supports(array $normalizedCommand): bool
    {
        return
            array_key_exists('identifier', $normalizedCommand)
            && (array_key_exists('min_value', $normalizedCommand) || array_key_exists('max_value', $normalizedCommand));
    }

    public function create(array $normalizedCommand): AbstractEditAttributeCommand
    {
        if (!$this->supports($normalizedCommand)) {
            throw new \RuntimeException('Impossible to create an edit min value property command.');
        }

        $minValue = $this->stringOrNull($normalizedCommand, 'min_value');
        $maxValue = $this->stringOrNull($normalizedCommand, 'max_value');
        return new EditMinMaxValueCommand(
            $normalizedCommand['identifier'],
            $minValue,
            $maxValue
        );
    }

    private function stringOrNull(array $normalizedCommand, string $key)
    {
        return isset($normalizedCommand[$key]) && "" !== $normalizedCommand[$key] ? (string)$normalizedCommand[$key] : null;
    }
}
