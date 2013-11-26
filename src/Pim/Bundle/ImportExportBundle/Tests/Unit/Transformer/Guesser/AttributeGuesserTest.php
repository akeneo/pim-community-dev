<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Transformer\Guesser;

use Pim\Bundle\ImportExportBundle\Transformer\Guesser\AttributeGuesser;
/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGuesserTest extends GuesserTestCase
{
    protected $attribute;

    protected function setUp()
    {
        parent::setUp();
        $this->attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductAttribute');
        $this->attribute->expects($this->any())
            ->method('getBackendType')
            ->will($this->returnValue('backend_type'));
    }

    public function testMatching()
    {
        $columnInfo = $this->getColumnInfoMock(array('attribute' => $this->attribute));
        $guesser = new AttributeGuesser($this->transformer, 'class', 'backend_type');

        $this->assertEquals(
            array($this->transformer, array()),
            $guesser->getTransformerInfo($columnInfo, $this->metadata)
        );
    }

    public function testNoAttribute()
    {
        $columnInfo = $this->getColumnInfoMock();
        $guesser = new AttributeGuesser($this->transformer, 'class', 'backend_type');

        $this->assertNull($guesser->getTransformerInfo($columnInfo, $this->metadata));
    }

    public function testWrongClass()
    {
        $columnInfo = $this->getColumnInfoMock(array('attribute' => $this->attribute));
        $guesser = new AttributeGuesser($this->transformer, 'other_class', 'backend_type');

        $this->assertNull($guesser->getTransformerInfo($columnInfo, $this->metadata));
    }

    public function testWrongBackendType()
    {
        $columnInfo = $this->getColumnInfoMock(array('attribute' => $this->attribute));
        $guesser = new AttributeGuesser($this->transformer, 'class', 'other_backend_type');

        $this->assertNull($guesser->getTransformerInfo($columnInfo, $this->metadata));
    }
}
