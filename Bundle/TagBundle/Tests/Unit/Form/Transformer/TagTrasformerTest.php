<?php

namespace Oro\Bundle\TagBundle\Tests\Unit\Form\Transformer;

use Oro\Bundle\TagBundle\Form\Transformer\TagTransformer;

class TagTrasformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TagTransformer
     */
    protected $transformer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $manager;

    public function setUp()
    {
        $this->manager = $this->getMockBuilder('Oro\Bundle\TagBundle\Entity\TagManager')
            ->disableOriginalConstructor()->getMock();
        $this->transformer = new TagTransformer($this->manager);
    }

    public function tearDown()
    {
        unset($this->manager);
        unset($this->transformer);
    }

    /**
     * @dataProvider valueTransformProvider
     * @param $value
     */
    public function testReverseTransform($value)
    {
        $this->assertEquals($value, $this->transformer->reverseTransform($value));
    }

    /**
     * @return array
     */
    public function valueTransformProvider()
    {
        return array(
            'some string' => array('string'),
            'null'        => array(null),
            'some array'  => array(array('test array')),
        );
    }

    public function testTransform()
    {
        $entity = $this->getMock('Oro\Bundle\TagBundle\Entity\Taggable');
        $this->transformer->setEntity($entity);

        $resultArray = array(array('some key' => 'some value'));
        $phpUnit = $this;

        $this->manager->expects($this->once())
            ->method('getPreparedArray')
            ->will(
                $this->returnCallback(
                    function ($entityArg, $tagsArg) use ($phpUnit, $entity, $resultArray) {
                        $phpUnit->assertEquals($entity, $entityArg);
                        $phpUnit->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $tagsArg);

                        return $resultArray;
                    }
                )
            )
            ->will($this->returnValue($resultArray));

        $this->assertEquals($this->transformer->transform(array()), json_encode($resultArray));
    }
}
