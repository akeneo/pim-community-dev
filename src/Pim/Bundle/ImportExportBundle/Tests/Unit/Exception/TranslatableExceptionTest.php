<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\EventListener;

use Pim\Bundle\ImportExportBundle\Exception\TranslatableException;

/**
 * Test related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslatableExceptionTest extends \PHPUnit_Framework_TestCase
{
    protected $exception;
    protected $previousException;

    protected function setUp()
    {
        $this->previousException = new \Exception;
        $this->exception = new TranslatableException(
            'message %param1%',
            array('%param1%' => 'value1'),
            100,
            $this->previousException
        );
    }

    public function testGetRawMessage()
    {
        $this->assertEquals('message %param1%', $this->exception->getRawMessage());
    }

    public function testGetMessageParameters()
    {
        $this->assertEquals(array('%param1%' => 'value1'), $this->exception->getMessageParameters());
    }

    public function testGetMessage()
    {
        $this->assertEquals('message value1', $this->exception->getMessage());
    }

    public function testGetCode()
    {
        $this->assertEquals(100, $this->exception->getCode());
    }

    public function testGetPreviousException()
    {
        $this->assertSame($this->previousException, $this->exception->getPrevious());
    }

    public function testTranslateMessage()
    {
        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $translator->expects($this->once())
            ->method('trans')
            ->with(
                $this->equalTo('message %param1%'),
                $this->equalTo(array('%param1%' => 'value1'))
            )
            ->will($this->returnValue('SUCCESS'));
        $this->exception->translateMessage($translator);
        $this->assertEquals('SUCCESS', $this->exception->getMessage());
    }
}
