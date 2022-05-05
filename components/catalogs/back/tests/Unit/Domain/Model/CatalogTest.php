<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Unit\Domain\Model;

use Akeneo\Catalogs\Domain\Model\Catalog;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CatalogTest extends TestCase
{
    public function testItSerializes(): void
    {
        $catalog = new Catalog('db1079b6-f397-4a6a-bae4-8658e64ad47c', 'Store US');

        $result = $catalog->jsonSerialize();
        $expected = [
            'id' => 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'name' => 'Store US',
        ];

        $this->assertEquals($expected, $result);
    }
}
