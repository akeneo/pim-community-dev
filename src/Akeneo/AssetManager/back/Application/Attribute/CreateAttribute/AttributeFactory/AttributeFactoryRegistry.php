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

namespace Akeneo\AssetManager\Application\Attribute\CreateAttribute\AttributeFactory;

use Akeneo\AssetManager\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeFactoryRegistry implements AttributeFactoryRegistryInterface
{
    private array $factories;

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
