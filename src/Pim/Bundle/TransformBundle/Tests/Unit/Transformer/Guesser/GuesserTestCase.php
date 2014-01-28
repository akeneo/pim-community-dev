<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\Transformer\Guesser;

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
    protected $columnInfo;
    protected $propertyPath = 'property_path';

    protected function setUp()
    {
        $this->transformer = $this->getMock(
            'Pim\Bundle\TransformBundle\Transformer\Property\PropertyTransformerInterface'
        );
        $this->metadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadataInfo')
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadata->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('class'));
        $this->columnInfo = $this->getMock('Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface');
        $this->columnInfo->expects($this->any())
            ->method('getPropertyPath')
            ->will($this->returnValue($this->propertyPath));

    }
}
