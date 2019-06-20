<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Record\EditRecord\ValueUpdater;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\AbstractEditValueCommand;

interface ValueUpdaterRegistryInterface
{
    public function register(ValueUpdaterInterface $attributeUpdater): void;

    public function getUpdater(AbstractEditValueCommand $command): ValueUpdaterInterface;
}
