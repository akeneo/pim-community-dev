<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\Factory;

use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\Command\AbstractCreateAttributeCommand;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeFactoryRegistry implements AttributeFactoryRegistryInterface
{
    /** @var AttributeFactoryInterface */
    private $factories;

    public function __construct()
    {
        $this->factories = [];
    }

    public function register(AttributeFactoryInterface $factory): void
    {
        $this->factories[] = $factory;
    }

    public function getFactory(AbstractCreateAttributeCommand $command): AttributeFactoryInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($command)) {
                return $factory;
            }
        }

        throw new \RuntimeException(
            sprintf('There was no attribute factory found for command "%s"', get_class($command))
        );
    }
}
