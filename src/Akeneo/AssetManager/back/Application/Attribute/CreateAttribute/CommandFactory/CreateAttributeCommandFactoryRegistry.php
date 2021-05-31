<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Attribute\CreateAttribute\CommandFactory;

class CreateAttributeCommandFactoryRegistry implements CreateAttributeCommandFactoryRegistryInterface
{
    private array $factories;

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
