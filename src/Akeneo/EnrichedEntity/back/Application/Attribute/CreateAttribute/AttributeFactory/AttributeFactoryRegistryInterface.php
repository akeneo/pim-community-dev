<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\AttributeFactory;

use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;

interface AttributeFactoryRegistryInterface
{
    public function register(AttributeFactoryInterface $factory): void;

    public function getFactory(AbstractCreateAttributeCommand $command): AttributeFactoryInterface;
}
