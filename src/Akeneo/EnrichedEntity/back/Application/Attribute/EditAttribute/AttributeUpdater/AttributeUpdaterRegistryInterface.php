<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\AbstractEditAttributeCommand;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;

interface AttributeUpdaterRegistryInterface
{
    public function register(AttributeUpdaterInterface $attributeUpdater): void;

    public function getUpdater(AbstractAttribute $attribute, AbstractEditAttributeCommand $command): AttributeUpdaterInterface;
}
