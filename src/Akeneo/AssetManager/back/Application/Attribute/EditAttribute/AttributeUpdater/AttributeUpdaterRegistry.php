<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\AbstractEditAttributeCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeUpdaterRegistry implements AttributeUpdaterRegistryInterface
{
    /** @var AttributeUpdaterInterface[]  */
    private array $updaters = [];

    public function register(AttributeUpdaterInterface $attributeUpdater): void
    {
        $this->updaters[] = $attributeUpdater;
    }

    public function getUpdater(
        AbstractAttribute $attribute,
        AbstractEditAttributeCommand $command
    ): AttributeUpdaterInterface {
        foreach ($this->updaters as $updater) {
            if ($updater->supports($attribute, $command)) {
                return $updater;
            }
        }

        throw new \RuntimeException(
            sprintf(
                'There was no updater found to update the attribute "%s" with the given command',
                $command->identifier
            )
        );
    }
}
