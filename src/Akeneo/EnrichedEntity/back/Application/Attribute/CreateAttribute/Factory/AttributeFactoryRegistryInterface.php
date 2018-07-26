<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\Factory;

use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\Command\AbstractCreateAttributeCommand;

interface AttributeFactoryRegistryInterface
{
    public function register(AttributeFactoryInterface $factory): void;

    public function getFactory(AbstractCreateAttributeCommand $command): AttributeFactoryInterface;
}
