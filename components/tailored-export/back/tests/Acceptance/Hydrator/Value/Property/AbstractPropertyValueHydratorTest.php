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

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\Hydrator\Value\Property;

use Akeneo\Platform\TailoredExport\Infrastructure\Hydrator\Value\PropertyValueHydrator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractPropertyValueHydratorTest extends KernelTestCase
{
    public function setUp(): void
    {
        static::bootKernel(['debug' => false]);
    }

    protected function getHydrator(): PropertyValueHydrator
    {
        return static::getContainer()->get('Akeneo\Platform\TailoredExport\Infrastructure\Hydrator\Value\PropertyValueHydrator');
    }
}
