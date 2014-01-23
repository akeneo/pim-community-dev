<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Transformer\Guesser;

use Pim\Bundle\ImportExportBundle\Transformer\Guesser\TypeGuesser;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TypeGuesserTest extends GuesserTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->metadata->expects($this->any())
            ->method('getTypeOfField')
            ->with($this->equalTo('property_path'))
            ->will($this->returnValue('type'));
    }

    public function testMatching()
    {
        $this->metadata->expects($this->once())
            ->method('hasField')
            ->with($this->equalTo('property_path'))
            ->will($this->returnValue(true));
        $guesser = new TypeGuesser($this->transformer, 'type');
        $this->assertEquals(
            [$this->transformer, []],
            $guesser->getTransformerInfo($this->columnInfo, $this->metadata)
        );
    }

    public function testNotField()
    {
        $this->metadata->expects($this->once())
            ->method('hasField')
            ->with($this->equalTo('property_path'))
            ->will($this->returnValue(false));
        $guesser = new TypeGuesser($this->transformer, 'type');
        $this->assertNull($guesser->getTransformerInfo($this->columnInfo, $this->metadata));
    }

    public function testWrongType()
    {
        $this->metadata->expects($this->once())
            ->method('hasField')
            ->with($this->equalTo('property_path'))
            ->will($this->returnValue(true));
        $guesser = new TypeGuesser($this->transformer, 'other_type');
        $this->assertNull($guesser->getTransformerInfo($this->columnInfo, $this->metadata));
    }
}
