<?php

namespace Akeneo\Bundle\BatchBundle\Tests\Unit\Item;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class InvalidItemExceptionTest extends \PHPUnit_Framework_TestCase
{
    protected $exception;

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
