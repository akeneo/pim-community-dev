<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Transformer\Guesser;

use Pim\Bundle\ImportExportBundle\Transformer\Guesser\ChainedGuesser;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChainedGuesserTest extends GuesserTestCase
{
    protected $guesser;

    protected function setUp()
    {
        parent::setUp();
        $this->guesser = new ChainedGuesser();
    }

    public function testMatching()
    {
        $matchedTransformer = $this->getTransformerMock();
        $this->addGuessers(
            $this->getGuesserMock(),
            $this->getGuesserMock(),
            $this->getGuesserMock(true, $matchedTransformer, array('options')),
            $this->getGuesserMock(false),
            $this->getGuesserMock(false, $this->getTransformerMock()),
            $this->getGuesserMock(false)
        );

        $this->assertEquals(
            array($matchedTransformer, array('options')),
            $this->guesser->getTransformerInfo($this->columnInfo, $this->metadata)
        );
    }

    public function testNoMatches()
    {
        $this->addGuessers($this->getGuesserMock(), $this->getGuesserMock());
        $this->assertNull($this->guesser->getTransformerInfo($this->columnInfo, $this->metadata));
    }

    protected function addGuessers()
    {
        array_walk(func_get_args(), array($this->guesser, 'addGuesser'));
    }

    protected function getGuesserMock($called = true, $transformer = null, $options = array())
    {
        $guesser = $this->getMock('Pim\Bundle\ImportExportBundle\Transformer\Guesser\GuesserInterface');
        $value = (null !== $transformer)
            ? array($transformer, $options)
            : null;
        if ($called) {
            $guesser->expects($this->once())
                ->method('getTransformerInfo')
                ->will($this->returnValue($value));
        } else {
            $guesser->expects($this->never())
                ->method('getTransformerInfo');
        }

        return $guesser;
    }

    protected function getTransformerMock()
    {
        return $this->getMock('Pim\Bundle\ImportExportBundle\Transformer\Property\PropertyTransformerInterface');
    }
}
