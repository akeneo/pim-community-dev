<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Attribute\CreateAttribute\CommandFactory;

use Akeneo\AssetManager\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateOptionCollectionAttributeCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CreateOptionCollectionAttributeCommandFactory extends AbstractCreateAttributeCommandFactory
{
    public function supports(array $normalizedCommand): bool
    {
        return isset($normalizedCommand['type']) && OptionCollectionAttribute::ATTRIBUTE_TYPE === $normalizedCommand['type'];
    }

    public function create(array $normalizedCommand): AbstractCreateAttributeCommand
    {
        $this->checkCommonProperties($normalizedCommand);

        return new CreateOptionCollectionAttributeCommand(
            $normalizedCommand['asset_family_identifier'],
            $normalizedCommand['code'],
            $normalizedCommand['labels'] ?? [],
            $normalizedCommand['is_required'] ?? false,
            $normalizedCommand['is_read_only'] ?? false,
            $normalizedCommand['value_per_channel'] ?? false,
            $normalizedCommand['value_per_locale'] ?? false
        );
    }
}
