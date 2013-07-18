<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Serializer\Normalizer;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Serializer\SerializerInterface;

use Oro\Bundle\WorkflowBundle\Serializer\Normalizer\WorkflowItemDataNormalizer;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItemData;

class WorkflowItemDataNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var WorkflowItemDataNormalizer
     */
    protected $normalizer;

    protected function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializer = $this->getMock('Symfony\Component\Serializer\SerializerInterface');

        $this->normalizer = new WorkflowItemDataNormalizer($this->em);
        $this->normalizer->setSerializer($this->serializer);
    }

    public function testNormalize()
    {
        $data = new WorkflowItemData();
        $data->foo = 'bar';

        $this->assertEquals(array('foo' => 'bar'), $this->normalizer->normalize($data));
    }

    public function testDenormalize()
    {
        $data = new WorkflowItemData();
        $data->foo = 'bar';

        $this->assertEquals(
            $data,
            $this->normalizer->denormalize(array('foo' => 'bar'), 'Oro\Bundle\WorkflowBundle\Entity\WorkflowItemData')
        );
    }

    public function testSupportsNormalization()
    {
        $this->assertTrue($this->normalizer->supportsNormalization(new WorkflowItemData()));
        $this->assertTrue($this->normalizer->supportsNormalization(
            $this->getMock('Oro\Bundle\WorkflowBundle\Entity\WorkflowItemData'))
        );
        $this->assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    public function testSupportsDenormalization()
    {
        $data = array('foo' => 'bar');
        $this->assertTrue(
            $this->normalizer->supportsDenormalization(
                $data, 'Oro\Bundle\WorkflowBundle\Entity\WorkflowItemData'
            )
        );
        $this->assertTrue(
            $this->normalizer->supportsDenormalization(
                $data, $this->getMockClass('Oro\Bundle\WorkflowBundle\Entity\WorkflowItemData')
            )
        );
        $this->assertFalse(
            $this->normalizer->supportsDenormalization(
                $data, 'stdClass'
            )
        );
    }
}
