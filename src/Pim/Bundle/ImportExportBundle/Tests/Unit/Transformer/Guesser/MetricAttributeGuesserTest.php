<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Transformer\Guesser;

use Pim\Bundle\ImportExportBundle\Transformer\Guesser\MetricAttributeGuesser;

/**
 * Tests related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricAttributeGuesserTest extends GuesserTestCase
{
    public function testMatching()
    {
        $this->setAttributeMock();
        $this->attribute
            ->expects($this->any())
            ->method('getMetricFamily')
            ->will($this->returnValue('metric_family'));

        $guesser = new MetricAttributeGuesser($this->transformer, 'class', 'backend_type');

        $this->assertEquals(
            array($this->transformer, array(
                'family' => 'metric_family'
            )),
            $guesser->getTransformerInfo($this->columnInfo, $this->metadata)
        );
    }

    public function testNoAttribute()
    {
        $guesser = new MetricAttributeGuesser($this->transformer, 'class', 'backend_type');

        $this->assertNull($guesser->getTransformerInfo($this->columnInfo, $this->metadata));
    }

    public function testWrongClass()
    {
        $this->setAttributeMock();
        $guesser = new MetricAttributeGuesser($this->transformer, 'other_class', 'backend_type');

        $this->assertNull($guesser->getTransformerInfo($this->columnInfo, $this->metadata));
    }

    public function testWrongBackendType()
    {
        $this->setAttributeMock();
        $guesser = new MetricAttributeGuesser($this->transformer, 'class', 'other_backend_type');

        $this->assertNull($guesser->getTransformerInfo($this->columnInfo, $this->metadata));
    }

    protected function setAttributeMock()
    {
        $this->attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductAttribute');
        $this->attribute->expects($this->any())
            ->method('getBackendType')
            ->will($this->returnValue('backend_type'));

        $this->columnInfo->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($this->attribute));
    }
}
