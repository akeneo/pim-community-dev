<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Family;

use Akeneo\Catalogs\Infrastructure\Persistence\Family\GetFamilyLabelByCodeAndLocaleQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Family\GetFamilyLabelByCodeAndLocaleQuery
 */
class GetFamilyLabelByCodeAndLocaleQueryTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItReturnsFamilyLabel(): void
    {
        $this->createFamily(['code' => 'shoes', 'labels' => [
            'fr_FR' => 'Chaussures',
            'en_US' => 'Shoes',
        ]]);

        $result = self::getContainer()->get(GetFamilyLabelByCodeAndLocaleQuery::class)->execute('shoes', 'en_US');
        $this->assertEquals('Shoes', $result);
        $result = self::getContainer()->get(GetFamilyLabelByCodeAndLocaleQuery::class)->execute('shoes', 'fr_FR');
        $this->assertEquals('Chaussures', $result);
    }

    public function testItReturnsFamilyCodeWhenFamilyIsNotFound(): void
    {
        $result = self::getContainer()->get(GetFamilyLabelByCodeAndLocaleQuery::class)->execute('shoes', 'en_US');
        $this->assertEquals('[shoes]', $result);
    }

    public function testItReturnsFamilyCodeWhenLabelIsNotFound(): void
    {
        $this->createFamily(['code' => 'shoes', 'labels' => [
            'fr_FR' => 'Chaussures',
        ]]);

        $result = self::getContainer()->get(GetFamilyLabelByCodeAndLocaleQuery::class)->execute('shoes', 'en_US');
        $this->assertEquals('[shoes]', $result);
    }
}
