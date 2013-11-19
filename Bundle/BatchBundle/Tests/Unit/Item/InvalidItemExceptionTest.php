<?php

namespace Oro\Bundle\BatchBundle\Tests\Unit\Item;

use Oro\Bundle\BatchBundle\Item\InvalidItemException;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidItemExceptionTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->exception = new InvalidItemException(
            'Tango is down, I repeat...',
            array('foo' => 'fighter')
        );
    }

    public function testIsAnException()
    {
        $this->assertInstanceOf('\Exception', $this->exception);
    }

    public function testAccessors()
    {
        $this->assertEquals('Tango is down, I repeat...', $this->exception->getMessage());
        $this->assertEquals(array('foo' => 'fighter'), $this->exception->getItem());
    }
}
