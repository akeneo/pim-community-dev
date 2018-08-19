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

namespace Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory;

class EditAttributeCommandFactoryRegistry implements EditAttributeCommandFactoryRegistryInterface
{
    /** @var EditAttributeCommandFactoryInterface */
    private $factories;

    public function __construct()
    {
        $this->factories = [];
    }

    public function register(EditAttributeCommandFactoryInterface $factory): void
    {
        $this->factories[] = $factory;
    }

    public function getFactory(array $normalizedCommand): EditAttributeCommandFactoryInterface
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
