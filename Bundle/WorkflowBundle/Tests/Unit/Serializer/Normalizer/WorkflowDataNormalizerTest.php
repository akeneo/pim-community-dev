<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Serializer\Normalizer;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Symfony\Component\Serializer\SerializerInterface;

use Oro\Bundle\WorkflowBundle\Serializer\Normalizer\WorkflowDataNormalizer;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;

class WorkflowDataNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var WorkflowDataNormalizer
     */
    protected $normalizer;

    protected function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializer = $this->getMock('Oro\Bundle\WorkflowBundle\Serializer\WorkflowAwareSerializer');
        $this->serializer->expects($this->any())->method('getWorkflow')
            ->will($this->returnValue(new Workflow()));

        $this->normalizer = new WorkflowDataNormalizer($this->registry);
        $this->normalizer->setSerializer($this->serializer);
    }

    public function testNormalize()
    {
        $data = new WorkflowData();
        $data->foo = 'bar';

        $this->assertEquals(array('foo' => 'bar'), $this->normalizer->normalize($data));
    }

    public function testDenormalize()
    {
        $data = new WorkflowData();
        $data->foo = 'bar';

        $this->assertEquals(
            $data,
            $this->normalizer->denormalize(array('foo' => 'bar'), 'Oro\Bundle\WorkflowBundle\Model\WorkflowData')
        );
    }

    public function testSupportsNormalization()
    {
        $this->assertTrue($this->normalizer->supportsNormalization(new WorkflowData()));
        $this->assertTrue(
            $this->normalizer->supportsNormalization(
                $this->getMock('Oro\Bundle\WorkflowBundle\Model\WorkflowData')
            )
        );
        $this->assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    public function testSupportsDenormalization()
    {
        $data = array('foo' => 'bar');
        $this->assertTrue(
            $this->normalizer->supportsDenormalization(
                $data,
                'Oro\Bundle\WorkflowBundle\Model\WorkflowData'
            )
        );
        $this->assertTrue(
            $this->normalizer->supportsDenormalization(
                $data,
                $this->getMockClass('Oro\Bundle\WorkflowBundle\Model\WorkflowData')
            )
        );
        $this->assertFalse(
            $this->normalizer->supportsDenormalization(
                $data,
                'stdClass'
            )
        );
    }
}
