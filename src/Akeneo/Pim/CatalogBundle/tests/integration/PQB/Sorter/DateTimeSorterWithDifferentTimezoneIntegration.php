<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Sorter;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Sorter\Directions;

/**
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeSorterWithDifferentTimezoneIntegration extends AbstractProductQueryBuilderTestCase
{
    public static $timezone;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        static::$timezone = ini_get('date.timezone');

        ini_set('date.timezone', 'UTC');

        $this->createProduct('foo', []);
        sleep(2);

        ini_set('date.timezone', 'Asia/Shanghai');
        $this->createProduct('bar', []);
        sleep(2);

        ini_set('date.timezone', 'America/Los_Angeles');
        $this->createProduct('baz', []);
    }

    public function testSorterAscending()
    {
        $result = $this->executeSorter([['updated', Directions::ASCENDING]]);
        $this->assertOrder($result, ['foo', 'bar', 'baz']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([['updated', Directions::DESCENDING]]);
        $this->assertOrder($result, ['baz', 'bar', 'foo']);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\InvalidDirectionException
     * @expectedExceptionMessage Direction "A_BAD_DIRECTION" is not supported
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeSorter([['updated', 'A_BAD_DIRECTION']]);
    }

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        ini_set('date.timezone', static::$timezone);
    }
}
