<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\Item\Support;

use Pim\Bundle\BatchBundle\Item\Support\EchoWriter;

/**
 * Tests related to the EchoWriter class
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class EchoWriterTest extends \PHPUnit_Framework_TestCase
{
    protected $echoWriter = null;

    protected function setUp()
    {
        $this->echoWriter = new EchoWriter();
    }

    public function testWrite()
    {
        $items = array('item1', 'item2', 'item3');
        $this->expectOutputString("item1\nitem2\nitem3\n");

        $this->echoWriter->write($items);
    }
}
