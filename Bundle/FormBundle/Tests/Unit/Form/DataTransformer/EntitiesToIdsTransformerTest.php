<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Form\Type;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;

use Oro\Bundle\FormBundle\Form\DataTransformer\EntitiesToIdsTransformer;

class EntitiesToIdsTransformerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManager;

    /**
     * @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    private $classMetadata;
    /**
     * @var EntityRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;
    /**
     * @var QueryBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queryBuilder;
    /**
     * @var AbstractQuery|\PHPUnit_Framework_MockObject_MockObject
     */
    private $query;

    /**
     * @dataProvider transformDataProvider
     *
     * @param string $property
     * @param mixed  $value
     * @param mixed  $expectedValue
     */
    public function testTransform($property, $value, $expectedValue)
    {
        $transformer = new EntitiesToIdsTransformer($this->getMockEntityManager(), 'TestClass', $property, null);
        $this->assertEquals($expectedValue, $transformer->transform($value));
    }

    /**
     * @return array
     */
    public function transformDataProvider()
    {
        return array(
            'default' => array(
                'id',
                $this->createMockEntityList('id', array(1, 2, 3, 4)),
                array(1, 2, 3, 4)
            ),
            'code property' => array(
                'code',
                $this->createMockEntityList('code', array('a', 'b', 'c')),
                array('a', 'b', 'c')
            ),
            'empty' => array(
                'id',
                array(),
                array()
            ),
        );
    }

    /**
     * @expectedException \Symfony\Component\Form\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "array", "string" given
     */
    public function testTransformFailsWhenValueInNotAnArray()
    {
        $transformer = new EntitiesToIdsTransformer($this->getMockEntityManager(), 'TestClass', 'id', null);
        $transformer->transform('invalid value');
    }

    /**
     * @dataProvider reverseTransformDataProvider
     *
     * @param $className
     * @param $property
     * @param $queryBuilderCallback
     * @param $value
     * @param $expectedValue
     * @param array $expectedCalls
     */
    public function testReverseTransform(
        $className,
        $property,
        $queryBuilderCallback,
        $value,
        $expectedValue,
        array $expectedCalls
    ) {
        foreach ($expectedCalls as $key => $calls) {
            $this->addMockExpectedCalls($key, $calls);
        }

        $transformer = new EntitiesToIdsTransformer(
            $this->getMockEntityManager(),
            $className,
            $property,
            $queryBuilderCallback
        );

        ;
        $this->assertEquals($expectedValue, $transformer->reverseTransform($value));
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function reverseTransformDataProvider()
    {
        $self = $this;
        $entitiesId1234 = $this->createMockEntityList('id', array(1, 2, 3, 4));
        $entitiesCodeAbc = $this->createMockEntityList('code', array('a', 'b', 'c'));

        return array(
            'default' => array(
                'TestClass',
                null,
                null,
                array(1, 2, 3, 4),
                $entitiesId1234,
                'expectedCalls' => array(
                    'entityManager' => array(
                        array('getClassMetadata', array('TestClass'), array('self', 'getMockClassMetadata')),
                        array('getRepository', array('TestClass'), array('self', 'getMockRepository')),
                    ),
                    'classMetadata' => array(
                        array('getSingleIdentifierFieldName', array(), 'id'),
                    ),
                    'repository' => array(
                        array('createQueryBuilder', array('e'), array('self', 'getMockQueryBuilder')),
                    ),
                    'queryBuilder' => array(
                        array('where', array('e.id IN (:ids)'), array('self', 'getMockQueryBuilder')),
                        array('setParameter', array('ids'), array(1, 2, 3, 4)),
                        array('getQuery', array(), array('self', 'getMockQuery')),
                    ),
                    'query' => array(
                        array(
                            'execute',
                            array(),
                            $entitiesId1234
                        ),
                    )
                )
            ),
            'empty' => array(
                'TestClass',
                'id',
                null,
                array(),
                array(),
                'expectedCalls' => array(
                    'entityManager' => array(),
                    'classMetadata' => array(),
                    'repository' => array(),
                    'queryBuilder' => array(),
                    'query' => array()
                )
            ),
            'custom property' => array(
                'TestClass',
                'code',
                null,
                array('a', 'b', 'c'),
                $entitiesCodeAbc,
                'expectedCalls' => array(
                    'entityManager' => array(
                        array('getRepository', array('TestClass'), array('self', 'getMockRepository')),
                    ),
                    'classMetadata' => array(),
                    'repository' => array(
                        array('createQueryBuilder', array('e'), array('self', 'getMockQueryBuilder')),
                    ),
                    'queryBuilder' => array(
                        array('where', array('e.code IN (:ids)'), array('self', 'getMockQueryBuilder')),
                        array('setParameter', array('ids'), array(1, 2, 3, 4)),
                        array('getQuery', array(), array('self', 'getMockQuery')),
                    ),
                    'query' => array(
                        array(
                            'execute',
                            array(),
                            $entitiesCodeAbc
                        ),
                    )
                )
            ),
            'custom query builder callback' => array(
                'TestClass',
                null,
                function ($repository, array $ids) use ($self) {
                    $result = $repository->createQueryBuilder('o');
                    $result->where('o.id IN (:values)')->setParameter('values', $ids);

                    return $result;
                },
                array(1, 2, 3, 4),
                $entitiesId1234,
                'expectedCalls' => array(
                    'entityManager' => array(
                        array('getClassMetadata', array('TestClass'), array('self', 'getMockClassMetadata')),
                        array('getRepository', array('TestClass'), array('self', 'getMockRepository')),
                    ),
                    'classMetadata' => array(
                        array('getSingleIdentifierFieldName', array(), 'id'),
                    ),
                    'repository' => array(
                        array('createQueryBuilder', array('o'), array('self', 'getMockQueryBuilder')),
                    ),
                    'queryBuilder' => array(
                        array('where', array('o.id IN (:values)'), array('self', 'getMockQueryBuilder')),
                        array('setParameter', array('values'), array(1, 2, 3, 4)),
                        array('getQuery', array(), array('self', 'getMockQuery')),
                    ),
                    'query' => array(
                        array(
                            'execute',
                            array(),
                            $entitiesId1234
                        ),
                    )
                )
            ),
        );
    }

    /**
     * @dataProvider reverseTransformFailsDataProvider
     *
     * @param mixed  $className
     * @param mixed  $property
     * @param mixed  $queryBuilderCallback
     * @param mixed  $value
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     * @param array  $expectedCalls
     */
    public function testReverseTransformFails(
        $className,
        $property,
        $queryBuilderCallback,
        $value,
        $expectedException,
        $expectedExceptionMessage,
        array $expectedCalls
    ) {
        foreach ($expectedCalls as $key => $calls) {
            $this->addMockExpectedCalls($key, $calls);
        }

        $transformer = new EntitiesToIdsTransformer(
            $this->getMockEntityManager(),
            $className,
            $property,
            $queryBuilderCallback
        );

        $this->setExpectedException($expectedException, $expectedExceptionMessage);
        $transformer->reverseTransform($value);
    }

    /**
     * @return array
     */
    public function reverseTransformFailsDataProvider()
    {
        $entitiesId1234 = $this->createMockEntityList('id', array(1, 2, 3, 4));

        return array(
            'not array' => array(
                'TestClass',
                'id',
                null,
                '1,2,3,4,5',
                'Symfony\Component\Form\Exception\UnexpectedTypeException',
                'Expected argument of type "array", "string" given',
                'expectedCalls' => array(
                    'entityManager' => array(),
                    'classMetadata' => array(),
                    'repository' => array(),
                    'queryBuilder' => array(),
                )
            ),
            'entities count mismatch ids count' => array(
                'TestClass',
                'id',
                null,
                array(1, 2, 3, 4, 5),
                'Symfony\Component\Form\Exception\TransformationFailedException',
                'Could not find all entities for the given IDs',
                'expectedCalls' => array(
                    'entityManager' => array(
                        array('getRepository', array('TestClass'), array('self', 'getMockRepository')),
                    ),
                    'classMetadata' => array(),
                    'repository' => array(
                        array('createQueryBuilder', array('e'), array('self', 'getMockQueryBuilder')),
                    ),
                    'queryBuilder' => array(
                        array('where', array('e.id IN (:ids)'), array('self', 'getMockQueryBuilder')),
                        array('setParameter', array('ids'), array(1, 2, 3, 4, 5)),
                        array('getQuery', array(), array('self', 'getMockQuery')),
                    ),
                    'query' => array(
                        array('execute', array(), $entitiesId1234),
                    )
                )
            ),
            'invalid query builder callback' => array(
                'TestClass',
                'id',
                function () {
                    return new \stdClass();
                },
                array(1, 2, 3, 4),
                'Symfony\Component\Form\Exception\UnexpectedTypeException',
                'Expected argument of type "Doctrine\ORM\QueryBuilder", "stdClass" given',
                'expectedCalls' => array(
                    'entityManager' => array(
                        array('getRepository', array('TestClass'), array('self', 'getMockRepository')),
                    ),
                    'classMetadata' => array(),
                    'repository' => array(),
                    'queryBuilder' => array(),
                    'query' => array()
                )
            )
        );
    }

    /**
     * @expectedException \Symfony\Component\Form\Exception\FormException
     * @expectedExceptionMessage Cannot get id property path of entity. "TestClass" has composite primary key.
     */
    public function testCreateFailsWhenCannotGetIdProperty()
    {
        $className = 'TestClass';

        $classMetadata = $this->getMockClassMetadata();
        $classMetadata->expects($this->once())->method('getSingleIdentifierFieldName')
            ->will($this->throwException(new \Doctrine\ORM\Mapping\MappingException()));

        $em = $this->getMockEntityManager();
        $em->expects($this->once())->method('getClassMetadata')
            ->with($className)->will($this->returnValue($classMetadata));

        new EntitiesToIdsTransformer($em, $className, null, null);
    }

    /**
     * @expectedException \Symfony\Component\Form\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "callable", "array" given
     */
    public function testCreateFailsWhenQueryBuilderCallbackIsNotCallable()
    {
        new EntitiesToIdsTransformer($this->getMockEntityManager(), 'TestClass', 'id', array());
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject|string $mock
     * @param array                                           $expectedCalls
     */
    private function addMockExpectedCalls($mock, array $expectedCalls)
    {
        if (is_string($mock)) {
            $mockGetter = 'getMock' . ucfirst($mock);
            $mock = $this->$mockGetter($mock);
        }
        $index = 0;
        if ($expectedCalls) {
            foreach ($expectedCalls as $expectedCall) {
                list($method, $arguments, $result) = $expectedCall;

                if (is_callable($result)) {
                    $result = call_user_func($result);
                }

                $methodExpectation = $mock->expects($this->at($index++))->method($method);
                $methodExpectation = call_user_func_array(array($methodExpectation, 'with'), $arguments);
                $methodExpectation->will($this->returnValue($result));
            }
        } else {
            $mock->expects($this->never())->method($this->anything());
        }
    }

    /**
     * Create list of mocked entities by id property name and values
     *
     * @param  string                                     $property
     * @param  array                                      $values
     * @return \PHPUnit_Framework_MockObject_MockObject[]
     */
    private function createMockEntityList($property, array $values)
    {
        $result = array();
        foreach ($values as $value) {
            $result[] = $this->createMockEntity($property, $value);
        }

        return $result;
    }

    /**
     * Create mock entity by id property name and value
     *
     * @param  string                                   $property
     * @param  mixed                                    $value
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockEntity($property, $value)
    {
        $getter = 'get' . ucfirst($property);
        $result = $this->getMock('MockEntity', array($getter));
        $result->expects($this->any())->method($getter)->will($this->returnValue($value));

        return $result;
    }

    /**
     * @return EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockEntityManager()
    {
        if (!$this->entityManager) {
            $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                ->disableOriginalConstructor()
                ->setMethods(array('getClassMetadata', 'getRepository'))
                ->getMockForAbstractClass();
        }

        return $this->entityManager;
    }

    /**
     * @return ClassMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockClassMetadata()
    {
        if (!$this->classMetadata) {
            $this->classMetadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
                ->disableOriginalConstructor()
                ->setMethods(array('getSingleIdentifierFieldName'))
                ->getMockForAbstractClass();
        }

        return $this->classMetadata;
    }

    /**
     * @return EntityRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockRepository()
    {
        if (!$this->repository) {
            $this->repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
                ->disableOriginalConstructor()
                ->setMethods(array('createQueryBuilder'))
                ->getMockForAbstractClass();
        }

        return $this->repository;
    }

    /**
     * @return QueryBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockQueryBuilder()
    {
        if (!$this->queryBuilder) {
            $this->queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
                ->disableOriginalConstructor()
                ->setMethods(array('where', 'setParameter', 'getQuery'))
                ->getMockForAbstractClass();
        }

        return $this->queryBuilder;
    }

    /**
     * @return AbstractQuery|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockQuery()
    {
        if (!$this->query) {
            $this->query= $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
                ->disableOriginalConstructor()
                ->setMethods(array('execute'))
                ->getMockForAbstractClass();
        }

        return $this->query;
    }
}
