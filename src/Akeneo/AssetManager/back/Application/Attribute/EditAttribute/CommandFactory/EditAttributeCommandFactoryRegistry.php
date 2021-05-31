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

namespace Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory;

class EditAttributeCommandFactoryRegistry implements EditAttributeCommandFactoryRegistryInterface
{
    /** @var EditAttributeCommandFactoryInterface[] */
    private array $factories = [];

    public function register(EditAttributeCommandFactoryInterface $factory): void
    {
        $this->factories[] = $factory;
    }

    public function getFactories(array $normalizedCommand): array
    {
        $factories = [];
        foreach ($this->factories as $factory) {
            if ($factory->supports($normalizedCommand)) {
                $factories[] = $factory;
            }
        }

        return $factories;
    }
}
