<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Reader;

use Pim\Bundle\ImportExportBundle\Reader\ORMCursorReader;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ORMCursorReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceOfItemReaderInterface()
    {
        $this->assertInstanceOf('Pim\Bundle\BatchBundle\Item\ItemReaderInterface', new ORMCursorReader);
    }

    public function testRead()
    {
        $reader = new ORMCursorReader;
        $query  = $this->getQueryMock();
        $result = $this->getIterableResultMock(
            array(
                $item1 = 'foo',
                $item2 = 'bar',
                $item3 = 'baz',
            )
        );

        $query->expects($this->any())
            ->method('iterate')
            ->will($this->returnValue($result));

        $reader->setQuery($query);
        $this->assertEquals($item1, $reader->read());
        $this->assertEquals($item2, $reader->read());
        $this->assertEquals($item3, $reader->read());
        $this->assertNull($reader->read());
    }

    private function getQueryMock()
    {
        return $this
            ->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->setMethods(array('_doExecute', 'getSQL', 'iterate'))
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getIterableResultMock(array $results)
    {
        $mock = $this
            ->getMockBuilder('Doctrine\ORM\Internal\Hydration\IterableResult')
            ->disableOriginalConstructor()
            ->getMock();

        foreach ($results as $index => $result) {
            $mock->expects($this->at($index))
                ->method('next')
                ->will($this->returnValue($result));
        }

        return $mock;
    }
}
