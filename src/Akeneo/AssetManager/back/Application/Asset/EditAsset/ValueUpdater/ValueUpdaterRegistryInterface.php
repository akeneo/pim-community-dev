<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\AbstractEditValueCommand;

interface ValueUpdaterRegistryInterface
{
    public function register(ValueUpdaterInterface $attributeUpdater): void;

    public function getUpdater(AbstractEditValueCommand $command): ValueUpdaterInterface;
}
