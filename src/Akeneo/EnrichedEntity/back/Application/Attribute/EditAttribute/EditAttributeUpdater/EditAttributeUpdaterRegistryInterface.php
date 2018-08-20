<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\EditAttributeUpdater;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\AbstractEditAttributeCommand;

interface EditAttributeUpdaterRegistryInterface
{
    public function register(EditAttributeUpdaterInterface $editAttributeAdapter): void;

    public function getAdapter(AbstractEditAttributeCommand $command): EditAttributeUpdaterInterface;
}
