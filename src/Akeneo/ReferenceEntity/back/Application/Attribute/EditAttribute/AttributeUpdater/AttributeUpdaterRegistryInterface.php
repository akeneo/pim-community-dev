<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\AbstractEditAttributeCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;

interface AttributeUpdaterRegistryInterface
{
    public function register(AttributeUpdaterInterface $attributeUpdater): void;

    public function getUpdater(AbstractAttribute $attribute, AbstractEditAttributeCommand $command): AttributeUpdaterInterface;
}
