<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Transformer\Guesser;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GuesserTestCase extends \PHPUnit_Framework_TestCase
{
    protected $transformer;
    protected $metadata;

    protected function setUp()
    {
        $this->transformer = $this->getMock(
            'Pim\Bundle\ImportExportBundle\Transformer\Property\PropertyTransformerInterface'
        );
        $this->metadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadataInfo')
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadata->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('class'));
    }

    protected function getColumnInfoMock(array $array = array())
    {
        $info = $this->getMockBuilder('Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo')
            ->disableOriginalConstructor()
            ->getMock();
        $info->expects($this->any())
            ->method(('offsetExists'))
            ->will(
                $this->returnCallback(
                    function ($offset) use ($array) {
                        return array_key_exists($offset, $array);
                    }
                )
            );
        $info->expects($this->any())
            ->method(('offsetGet'))
            ->will(
                $this->returnCallback(
                    function ($offset) use ($array) {
                        return $array[$offset];
                    }
                )
            );

        return $info;
    }
}
