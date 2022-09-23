<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Syndication\Test\Acceptance\Hydrator\Value\Property;

use Akeneo\Platform\Syndication\Infrastructure\Hydrator\Value\PropertyValueHydrator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractPropertyValueHydratorTest extends KernelTestCase
{
    public function setUp(): void
    {
        static::bootKernel(['debug' => false]);
    }

    protected function getHydrator(): PropertyValueHydrator
    {
        return static::$container->get('Akeneo\Platform\Syndication\Infrastructure\Hydrator\Value\PropertyValueHydrator');
    }
}
