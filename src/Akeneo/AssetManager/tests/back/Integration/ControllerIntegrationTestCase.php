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

namespace Akeneo\AssetManager\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * This class is used for running integration tests testing the web controllers.
 *
 * Every service definition of repositories or query functions uses the in memory implementation that manipulates
 * objects.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
abstract class ControllerIntegrationTestCase extends KernelTestCase
{
    protected function setUp(): void
    {
        static::bootKernel(['environment' => 'test_fake', 'debug' => false]);
    }

    protected function get(string $service)
    {
        return self::$container->get($service);
    }
}
