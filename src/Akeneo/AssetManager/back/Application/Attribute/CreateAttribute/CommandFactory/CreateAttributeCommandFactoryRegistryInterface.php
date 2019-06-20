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

interface CreateAttributeCommandFactoryRegistryInterface
{
    public function register(CreateAttributeCommandFactoryInterface $factory): void;

    public function getFactory(array $normalizedCommand): CreateAttributeCommandFactoryInterface;
}
