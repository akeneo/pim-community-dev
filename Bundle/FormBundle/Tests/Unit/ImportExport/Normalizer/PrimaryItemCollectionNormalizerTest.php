<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\ImportExport\Serializer\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\FormBundle\ImportExport\Serializer\Normalizer\PrimaryItemCollectionNormalizer;

class PrimaryItemCollectionNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $serializer;

    /**
     * @var PrimaryItemCollectionNormalizer
     */
    protected $normalizer;

    protected function setUp()
    {
        $this->serializer = $this->getMock('Oro\Bundle\ImportExportBundle\Serializer\Serializer');
        $this->normalizer = new PrimaryItemCollectionNormalizer();
        $this->normalizer->setSerializer($this->serializer);
    }

    /**
     * @dataProvider supportsNormalizationDataProvider
     */
    public function testSupportsNormalization($data, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->normalizer->supportsNormalization($data, null));
    }

    public function supportsNormalizationDataProvider()
    {
        $primaryItem = $this->getMock(PrimaryItemCollectionNormalizer::PRIMARY_ITEM_TYPE);
        return array(
            array('stdClass', false),
            array(new ArrayCollection(), false),
            array(new ArrayCollection(array($primaryItem, new \stdClass())), false),
            array(new ArrayCollection(array($primaryItem)), true),
        );
    }

    /**
     * @dataProvider supportsDenormalizationDataProvider
     */
    public function testSupportsDenormalization($type, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->normalizer->supportsDenormalization(array(), $type));
    }

    public function supportsDenormalizationDataProvider()
    {
        $primaryItemClass = $this->getMockClass(PrimaryItemCollectionNormalizer::PRIMARY_ITEM_TYPE);
        return array(
            array('stdClass', false),
            array('ArrayCollection', false),
            array('Doctrine\\Common\\Collections\\ArrayCollection', false),
            array('Doctrine\\Common\\Collections\\ArrayCollection<Foo>', false),
            array("ArrayCollection<$primaryItemClass>", true),
            array("Doctrine\\Common\\Collections\\ArrayCollection<$primaryItemClass>", true),
        );
    }

    public function testNormalize()
    {
        $format = null;
        $context = array('context');

        $firstItem = $this->getMockPrimaryItem(false);
        $secondPrimaryItem = $this->getMockPrimaryItem(true);
        $thirdItem = $this->getMockPrimaryItem(false);

        $data = new ArrayCollection(array($firstItem, $secondPrimaryItem, $thirdItem));
        $this->serializer->expects($this->exactly(3))
            ->method('normalize')
            ->will(
                $this->returnValueMap(
                    array(
                        array($firstItem, $format, $context, 'first'),
                        array($secondPrimaryItem, $format, $context, 'second_primary'),
                        array($thirdItem, $format, $context, 'third'),
                    )
                )
            );

        $this->assertEquals(
            array('second_primary', 'first', 'third'),
            $this->normalizer->normalize($data, $format, $context)
        );
    }

    protected function getMockPrimaryItem($primary)
    {
        $result = $this->getMock(PrimaryItemCollectionNormalizer::PRIMARY_ITEM_TYPE);
        $result->expects($this->once())->method('isPrimary')->will($this->returnValue($primary));
        return $result;
    }


    public function testDenormalizeWithItemType()
    {
        $format = null;
        $context = array('context');

        $primaryItemClass = $this->getMockClass(PrimaryItemCollectionNormalizer::PRIMARY_ITEM_TYPE);

        $firstElement = $this->getMock(PrimaryItemCollectionNormalizer::PRIMARY_ITEM_TYPE);
        $firstElement->expects($this->once())->method('setPrimary')->with(true); // first is primary

        $secondElement = $this->getMock(PrimaryItemCollectionNormalizer::PRIMARY_ITEM_TYPE);
        $secondElement->expects($this->once())->method('setPrimary')->with(false);

        $thirdElement = $this->getMock(PrimaryItemCollectionNormalizer::PRIMARY_ITEM_TYPE);
        $thirdElement->expects($this->once())->method('setPrimary')->with(false);

        $this->serializer->expects($this->exactly(3))
            ->method('denormalize')
            ->will(
                $this->returnValueMap(
                    array(
                        array('first', $primaryItemClass, $format, $context, $firstElement),
                        array('second', $primaryItemClass, $format, $context, $secondElement),
                        array('third', $primaryItemClass, $format, $context, $thirdElement),
                    )
                )
            );

        $this->assertEquals(
            new ArrayCollection(array($firstElement, $secondElement, $thirdElement)),
            $this->normalizer->denormalize(
                array('first', 'second', 'third'),
                "ArrayCollection<$primaryItemClass>",
                $format,
                $context
            )
        );
    }
}
