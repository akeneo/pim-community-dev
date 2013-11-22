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
     * @param array $options
     * @param string $expectedMessage
     * @dataProvider initializeExceptionDataProvider
     */
    public function testInitializeException(array $options, $expectedMessage)
    {
        $this->setExpectedException(
            '\Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException',
            $expectedMessage
        );

        $this->action->initialize($options);
    }

    public function initializeExceptionDataProvider()
    {
        return array(
            'no class name' => array(
                'options' => array(
                    'some' => 1,
                ),
                'message' => 'Class name parameter is required'
            ),
            'no attribute' => array(
                'options' => array(
                    'class' => 'stdClass',
                ),
                'message' => 'Attribute name parameter is required'
            ),
            'invalid attribute' => array(
                array(
                    'class' => 'stdClass',
                    'identifier' => 1,
                    'attribute' => 'string',
                ),
                'message' => 'Attribute must be valid property definition.'
            ),
            'no identifier' => array(
                'options' => array(
                    'class' => 'stdClass',
                    'attribute' => $this->getPropertyPath(),
                ),
                'message' => 'One of parameters "identifier", "where" or "order_by" must be defined'
            ),
            'invalid where' => array(
                'options' => array(
                    'class' => 'stdClass',
                    'attribute' => $this->getPropertyPath(),
                    'where' => 'scalar_data'
                ),
                'message' => 'Parameter "where" must be array'
            ),
            'invalid order_by' => array(
                'options' => array(
                    'class' => 'stdClass',
                    'attribute' => $this->getPropertyPath(),
                    'order_by' => 'scalar_data'
                ),
                'message' => 'Parameter "order_by" must be array'
            ),
        );
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

    /**
     * @param array $source
     * @param array $expected
     * @dataProvider initializeDataProvider
     */
    public function testInitialize(array $source, array $expected)
    {
        $this->assertEquals($this->action, $this->action->initialize($source));
        $this->assertAttributeEquals($expected, 'options', $this->action);
    }

    public function initializeDataProvider()
    {
        return array(
            'entity identifier' => array(
                'source' => array(
                    'class' => 'stdClass',
                    'identifier' => 1,
                    'attribute' => $this->getPropertyPath(),
                ),
                'expected' => array(
                    'class' => 'stdClass',
                    'identifier' => 1,
                    'attribute' => $this->getPropertyPath(),
                    'where' => array(),
                    'order_by' => array(),
                    'case_insensitive' => false,
                ),
            ),
            'where and order by' => array(
                'source' => array(
                    'class' => 'stdClass',
                    'where' => array('name' => 'qwerty'),
                    'order_by' => array('date' => 'asc'),
                    'attribute' => $this->getPropertyPath(),
                    'case_insensitive' => true,
                ),
                'expected' => array(
                    'class' => 'stdClass',
                    'where' => array('name' => 'qwerty'),
                    'order_by' => array('date' => 'asc'),
                    'attribute' => $this->getPropertyPath(),
                    'case_insensitive' => true,
                ),
            )
        );
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
        if (!empty($options['case_insensitive'])) {
            $expectedIdentifier = strtolower($expectedIdentifier);
        }
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
            'scalar_case_insensitive_identifier' => array(
                'options' => array(
                    'class' => '\stdClass',
                    'identifier' => 'DATA',
                    'attribute' => new PropertyPath('entity_attribute'),
                    'case_insensitive' => true,
                )
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
     * @param bool $caseInsensitive
     * @dataProvider executeWithConditionsDataProvider
     */
    public function testExecuteWithWhereAndOrderBy($caseInsensitive)
    {
        $options = array(
            'class' => '\stdClass',
            'where' => array('name' => 'Qwerty'),
            'attribute' => new PropertyPath('entity'),
            'order_by' => array('createdDate' => 'asc'),
            'case_insensitive' => $caseInsensitive
        );

        $context = new ItemStub();
        $entity = new \stdClass();

        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')->disableOriginalConstructor()
            ->setMethods(array('getOneOrNullResult'))->getMockForAbstractClass();
        $query->expects($this->once())->method('getOneOrNullResult')->will($this->returnValue($entity));

        $expectedField = !empty($options['case_insensitive']) ? 'LOWER(e.name)' : 'e.name';
        $expectedValue = !empty($options['case_insensitive'])
            ? strtolower($options['where']['name'])
            : $options['where']['name'];
        $expectedParameter = 'parameter_0';
        $expectedOrder = 'e.createdDate';

        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')->disableOriginalConstructor()->getMock();
        $queryBuilder->expects($this->once())->method('andWhere')
            ->with("$expectedField = :$expectedParameter")->will($this->returnSelf());
        $queryBuilder->expects($this->once())->method('setParameter')
            ->with($expectedParameter, $expectedValue)->will($this->returnSelf());
        $queryBuilder->expects($this->once())->method('orderBy')
            ->with($expectedOrder, $options['order_by']['createdDate'])->will($this->returnSelf());
        $queryBuilder->expects($this->once())->method('getQuery')->will($this->returnValue($query));

        $repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')->disableOriginalConstructor()->getMock();
        $repository->expects($this->once())->method('createQueryBuilder')
            ->with('e')->will($this->returnValue($queryBuilder));

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $em->expects($this->once())->method('getRepository')
            ->with($options['class'])->will($this->returnValue($repository));

        $this->registry->expects($this->once())->method('getManagerForClass')
            ->with($options['class'])->will($this->returnValue($em));

        $this->action->initialize($options);
        $this->action->execute($context);

        $attributeName = (string)$options['attribute'];
        $this->assertEquals($entity, $context->$attributeName);
    }

    /**
     * @return array
     */
    public function executeWithConditionsDataProvider()
    {
        return array(
            'case sensitive' => array(
                'caseInsensitive' => false,
            ),
            'case insensitive' => array(
                'caseInsensitive' => true,
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
}
