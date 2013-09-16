<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\ImportExport\Serializer\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\CollectionNormalizer;

class CollectionNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $serializer;

    /**
     * @var CollectionNormalizer
     */
    protected $normalizer;

    protected function setUp()
    {
        $this->serializer = $this->getMock('Symfony\Component\Serializer\SerializerInterface');
        $this->normalizer = new CollectionNormalizer();
        $this->normalizer->setSerializer($this->serializer);
    }

    public function testSupportsNormalization()
    {
        $this->assertFalse($this->normalizer->supportsNormalization(new \stdClass()));

        $collection = $this->getMock('Doctrine\Common\Collections\Collection');
        $this->assertTrue($this->normalizer->supportsNormalization($collection));
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
        return array(
            array('stdClass', false),
            array('ArrayCollection', true),
            array('Doctrine\Common\Collections\ArrayCollection', true),
            array('Doctrine\Common\Collections\ArrayCollection<Foo>', true),
            array('Doctrine\Common\Collections\ArrayCollection<Foo\Bar\Baz>', true),
            array('ArrayCollection<ArrayCollection<Foo\Bar\Baz>>', true),
        );
    }

    public function testNormalize()
    {
        $format = null;
        $context = array('context');

        $firstElement = $this->getMock('FirstObject');
        $secondElement = $this->getMock('SecondObject');
        $data = new ArrayCollection(array($firstElement, $secondElement));

        $this->serializer->expects($this->exactly(2))
            ->method('serialize')
            ->will(
                $this->returnValueMap(
                    array(
                        array($firstElement, $format, $context, 'first'),
                        array($secondElement, $format, $context, 'second'),
                    )
                )
            );

        $this->assertEquals(
            array('first', 'second'),
            $this->normalizer->normalize($data, $format, $context)
        );
    }

    public function testDenormalizeNotArray()
    {
        $this->serializer->expects($this->never())->method($this->anything());
        $this->assertEquals(
            new ArrayCollection(),
            $this->normalizer->denormalize('string', null)
        );
    }

    public function testDenormalizeSimple()
    {
        $this->serializer->expects($this->never())->method($this->anything());
        $data = array('foo', 'bar');
        $this->assertEquals(
            new ArrayCollection($data),
            $this->normalizer->denormalize($data, 'ArrayCollection', null)
        );
    }

    public function testDenormalizeWithItemType()
    {
        $format = null;
        $context = array();

        $fooEntity = new \stdClass();
        $barEntity = new \stdClass();

        $this->serializer->expects($this->exactly(2))
            ->method('deserialize')
            ->will(
                $this->returnValueMap(
                    array(
                        array('foo', 'ItemType', $format, $context, $fooEntity),
                        array('bar', 'ItemType', $format, $context, $barEntity),
                    )
                )
            );

        $this->assertEquals(
            new ArrayCollection(array($fooEntity, $barEntity)),
            $this->normalizer->denormalize(
                array('foo', 'bar'),
                'ArrayCollection<ItemType>',
                $format,
                $context
            )
        );
    }
}
