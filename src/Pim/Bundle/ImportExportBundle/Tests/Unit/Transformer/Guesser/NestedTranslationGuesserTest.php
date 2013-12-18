<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Transformer\Guesser;

use Pim\Bundle\ImportExportBundle\Transformer\Guesser\NestedTranslationGuesser;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NestedTranslationGuesserTest extends GuesserTestCase
{
    protected $guesser;

    protected function setUp()
    {
        parent::setUp();
        $this->guesser = new NestedTranslationGuesser($this->transformer);
    }

    public function testMatching()
    {
        $this->columnInfo->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('labels'));
        $this->metadata->expects($this->once())
            ->method('hasAssociation')
            ->with($this->equalTo('translations'))
            ->will($this->returnValue(true));
        $this->assertEquals(
            array($this->transformer, array('propertyPath' => 'label')),
            $this->guesser->getTransformerInfo($this->columnInfo, $this->metadata)
        );
    }

    public function testNotLabels()
    {
        $this->columnInfo->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('other_field'));
        $this->metadata->expects($this->any())
            ->method('hasAssociation')
            ->with($this->equalTo('translations'))
            ->will($this->returnValue(true));
        $this->assertNull($this->guesser->getTransformerInfo($this->columnInfo, $this->metadata));
    }

    public function testNoTranslations()
    {
        $this->columnInfo->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('labels'));
        $this->metadata->expects($this->once())
            ->method('hasAssociation')
            ->with($this->equalTo('translations'))
            ->will($this->returnValue(false));
        $this->assertNull($this->guesser->getTransformerInfo($this->columnInfo, $this->metadata));
    }
}
