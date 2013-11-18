<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Action;

use Symfony\Component\PropertyAccess\PropertyPath;

use Oro\Bundle\WorkflowBundle\Model\Action\RequestEntity;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Stub\ItemStub;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;

class RequestEntityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RequestEntity
     */
    protected $action;

    /**
     * @var ContextAccessor
     */
    protected $contextAccessor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    protected function setUp()
    {
        $this->contextAccessor = new ContextAccessor();

        $this->registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->action = new RequestEntity($this->contextAccessor, $this->registry);
    }

    protected function tearDown()
    {
        unset($this->contextAccessor);
        unset($this->registry);
        unset($this->action);
    }

    /**
     * @return PropertyPath
     */
    protected function getPropertyPath()
    {
        return $this->getMockBuilder('Symfony\Component\PropertyAccess\PropertyPath')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Class name parameter is required
     */
    public function testInitializeExceptionNoClassName()
    {
        $this->action->initialize(
            array(
                'some' => 1,
                'attribute' => $this->getPropertyPath(),
            )
        );
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Identifier parameter is required
     */
    public function testInitializeExceptionNoIdentifier()
    {
        $this->action->initialize(
            array(
                'class' => 'stdClass',
            )
        );
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Attribute name parameter is required
     */
    public function testInitializeExceptionNoAttribute()
    {
        $this->action->initialize(
            array(
                'class' => 'stdClass',
                'identifier' => 1,
            )
        );
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Attribute must be valid property definition.
     */
    public function testInitializeExceptionInvalidAttribute()
    {
        $this->action->initialize(
            array(
                'class' => 'stdClass',
                'identifier' => 1,
                'attribute' => 'string',
            )
        );
    }

    public function testInitialize()
    {
        $options = array(
            'class' => 'stdClass',
            'identifier' => 1,
            'attribute' => $this->getPropertyPath(),
        );
        $this->assertEquals($this->action, $this->action->initialize($options));
        $this->assertAttributeEquals($options, 'options', $this->action);
    }

    /**
     * @param array $options
     * @param array $data
     * @dataProvider executeDataProvider
     */
    public function testExecute(array $options, array $data = array())
    {
        $context = new ItemStub($data);
        $entity = new \stdClass();

        $expectedIdentifier = $this->convertIdentifier($context, $options['identifier']);
        $this->assertNotEmpty($expectedIdentifier);

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->once())
            ->method('getReference')
            ->with($options['class'], $expectedIdentifier)
            ->will($this->returnValue($entity));

        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with($options['class'])
            ->will($this->returnValue($em));

        $this->action->initialize($options);
        $this->action->execute($context);

        $attributeName = (string)$options['attribute'];
        $this->assertEquals($entity, $context->$attributeName);
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return array(
            'scalar_identifier' => array(
                'options' => array(
                    'class' => '\stdClass',
                    'identifier' => 1,
                    'attribute' => new PropertyPath('entity_attribute'),
                )
            ),
            'scalar_attribute_identifier' => array(
                'options' => array(
                    'class' => '\stdClass',
                    'identifier' => new PropertyPath('id'),
                    'attribute' => new PropertyPath('entity_attribute'),
                ),
                'data' => array(
                    'id' => 1
                ),
            ),
            'array_identifier' => array(
                'options' => array(
                    'class' => '\stdClass',
                    'identifier' => array(
                        'id'   => 1,
                        'name' => 'unique_key',
                    ),
                    'attribute' => new PropertyPath('entity_attribute'),
                )
            ),
            'array_attribute_identifier' => array(
                'options' => array(
                    'class' => '\stdClass',
                    'identifier' => array(
                        'id'   => new PropertyPath('id_attribute'),
                        'name' => new PropertyPath('name_attribute'),
                    ),
                    'attribute' => new PropertyPath('entity_attribute'),
                ),
                'data' => array(
                    'id_attribute'   => 1,
                    'name_attribute' => 'unique_key',
                ),
            ),
        );
    }

    /**
     * @param mixed $context
     * @param mixed $identifier
     * @return mixed
     */
    protected function convertIdentifier($context, $identifier)
    {
        if (is_array($identifier)) {
            foreach ($identifier as $key => $value) {
                $identifier[$key] = $this->contextAccessor->getValue($context, $value);
            }
        } else {
            $identifier = $this->contextAccessor->getValue($context, $identifier);
        }

        return $identifier;
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\NotManageableEntityException
     * @expectedExceptionMessage Entity class "\stdClass" is not manageable.
     */
    public function testExecuteNotManageableEntity()
    {
        $options = array(
            'class' => '\stdClass',
            'identifier' => 1,
            'attribute' => $this->getPropertyPath()
        );
        $context = new ItemStub(array());

        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with('\stdClass')
            ->will($this->returnValue(null));

        $this->action->initialize($options);
        $this->action->execute($context);
    }
}
