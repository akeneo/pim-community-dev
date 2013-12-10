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
    protected function setAttributeMock()
    {
        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductAttribute');
        $attribute->expects($this->any())
            ->method('getBackendType')
            ->will($this->returnValue('backend_type'));

        $this->columnInfo->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));
    }

    public function testMatching()
    {
        $this->setAttributeMock();
        $guesser = new AttributeGuesser($this->transformer, 'class', 'backend_type');

        $this->assertEquals(
            array($this->transformer, array()),
            $guesser->getTransformerInfo($this->columnInfo, $this->metadata)
        );
    }

    public function testNoAttribute()
    {
        $guesser = new AttributeGuesser($this->transformer, 'class', 'backend_type');

        $this->assertNull($guesser->getTransformerInfo($this->columnInfo, $this->metadata));
    }

    public function testWrongClass()
    {
        $this->setAttributeMock();
        $guesser = new AttributeGuesser($this->transformer, 'other_class', 'backend_type');

        $this->assertNull($guesser->getTransformerInfo($this->columnInfo, $this->metadata));
    }

    public function testWrongBackendType()
    {
        $this->setAttributeMock();
        $guesser = new AttributeGuesser($this->transformer, 'class', 'other_backend_type');

        $this->assertNull($guesser->getTransformerInfo($this->columnInfo, $this->metadata));
    }
}
