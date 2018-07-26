<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CommandFactory;

class CreateAttributeCommandFactoryRegistry implements CreateAttributeCommandFactoryRegistryInterface
{
    /** @var CreateAttributeCommandFactoryInterface */
    private $factories;

    public function __construct()
    {
        $this->factories = [];
    }

    public function register(CreateAttributeCommandFactoryInterface $factory): void
    {
        $this->factories[] = $factory;
    }

    public function getFactory(array $normalizedCommand): CreateAttributeCommandFactoryInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($normalizedCommand)) {
                return $factory;
            }
        }

        throw new \RuntimeException(
            sprintf('There was no create attribute command factory found for command  of type "%s"', $normalizedCommand['type'])
        );
    }
}
