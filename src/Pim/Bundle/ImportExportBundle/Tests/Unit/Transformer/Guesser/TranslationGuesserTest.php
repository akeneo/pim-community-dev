<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Transformer\Guesser;

use Pim\Bundle\ImportExportBundle\Transformer\Guesser\TranslationGuesser;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslationGuesserTest extends GuesserTestCase
{
    protected $guesser;

    protected function setUp()
    {
        parent::setUp();
        $this->guesser = new TranslationGuesser($this->transformer);
    }

    public function testMatching()
    {
        $this->columnInfo->expects($this->once())
            ->method('getSuffixes')
            ->will($this->returnValue(array('locale')));
        $this->metadata->expects($this->once())
            ->method('hasAssociation')
            ->with($this->equalTo('translations'))
            ->will($this->returnValue(true));
        $this->assertEquals(
            array($this->transformer, array()),
            $this->guesser->getTransformerInfo($this->columnInfo, $this->metadata)
        );
    }

    public function testNotSuffixes()
    {
        $this->columnInfo->expects($this->once())
            ->method('getSuffixes')
            ->will($this->returnValue(array()));
        $this->metadata->expects($this->any())
            ->method('hasAssociation')
            ->with($this->equalTo('translations'))
            ->will($this->returnValue(true));
        $this->assertNull($this->guesser->getTransformerInfo($this->columnInfo, $this->metadata));
    }

    public function testNoTranslations()
    {
        $this->columnInfo->expects($this->any())
            ->method('getSuffixes')
            ->will($this->returnValue(array('locale')));
        $this->metadata->expects($this->once())
            ->method('hasAssociation')
            ->with($this->equalTo('translations'))
            ->will($this->returnValue(false));
        $this->assertNull($this->guesser->getTransformerInfo($this->columnInfo, $this->metadata));
    }
}
