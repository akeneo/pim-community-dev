<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Test\Acceptance\UseCases\Attribute;

use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\ExecuteDataMappingHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AttributeTestCase extends KernelTestCase
{
    public function setUp(): void
    {
        static::bootKernel(['debug' => false]);
    }

    protected function getExecuteDataMappingHandler(): ExecuteDataMappingHandler
    {
        return self::getContainer()->get('akeneo.tailored_import.handler.execute_data_mapping');
    }
}
