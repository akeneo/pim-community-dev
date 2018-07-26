<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CommandFactory;

interface CreateAttributeCommandFactoryRegistryInterface
{
    public function register(CreateAttributeCommandFactoryInterface $factory): void;

    public function getFactory(array $normalizedCommand): CreateAttributeCommandFactoryInterface;
}
