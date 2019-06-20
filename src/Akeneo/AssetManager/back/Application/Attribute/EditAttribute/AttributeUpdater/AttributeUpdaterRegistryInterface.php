<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\AbstractEditAttributeCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;

interface AttributeUpdaterRegistryInterface
{
    public function register(AttributeUpdaterInterface $attributeUpdater): void;

    public function getUpdater(AbstractAttribute $attribute, AbstractEditAttributeCommand $command): AttributeUpdaterInterface;
}
