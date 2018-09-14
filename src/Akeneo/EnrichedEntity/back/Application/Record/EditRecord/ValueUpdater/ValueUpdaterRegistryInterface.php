<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Record\EditRecord\ValueUpdater;

use Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory\AbstractEditValueCommand;

interface ValueUpdaterRegistryInterface
{
    public function register(ValueUpdaterInterface $attributeUpdater): void;

    public function getUpdater(AbstractEditValueCommand $command): ValueUpdaterInterface;
}
