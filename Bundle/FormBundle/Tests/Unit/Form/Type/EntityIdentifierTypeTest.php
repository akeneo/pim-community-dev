<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Form\Type;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;

use Symfony\Component\Form\Tests\FormIntegrationTestCase;

use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;

class EntityIdentifierTypeTest extends FormIntegrationTestCase
{
    /**
     * @var EntityIdentifierType
     */
    private $type;

    /**
     * @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $managerRegistry;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManager;

    /**
     * @var EntitiesToIdsTransformer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entitiesToIdsTransformer;

    protected function setUp()
    {
        parent::setUp();
        $this->type = $this->getMockBuilder('Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType')
            ->setMethods(array('createEntitiesToIdsTransformer'))
            ->setConstructorArgs(array($this->getMockManagerRegistry()))
            ->getMock();
        $this->type->expects($this->any())->method('createEntitiesToIdsTransformer')
            ->will($this->returnValue($this->getMockEntitiesToIdsTransformer()));
    }

    /**
     * {@inheritDoc}
     */
    protected function getTestFormType()
    {
        return $this->type;
    }

    /**
     * @dataProvider bindDataProvider
     * @param mixed $bindData
     * @param mixed $formData
     * @param mixed $viewData
     * @param array $options
     * @param array $expectedCalls
     */
    public function testBindData(
        $bindData,
        $formData,
        $viewData,
        array $options,
        array $expectedCalls
    ) {
        if (isset($options['em']) && is_callable($options['em'])) {
            $options['em'] = call_user_func($options['em']);
        }

        foreach ($expectedCalls as $key => $calls) {
            $this->addMockExpectedCalls($key, $calls);
        }

        $form = $this->factory->create($this->getTestFormType(), null, $options);

        $form->bind($bindData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($formData, $form->getData());

        $view = $form->createView();
        $this->assertEquals($viewData, $view->vars['value']);
    }

    /**
     * Data provider for testBindData
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function bindDataProvider()
    {
        $self = $this;
        $entitiesId1234 = $this->createMockEntityList('id', array(1, 2, 3, 4));
        return array(
            'default' => array(
                '1,2,3,4',
                $entitiesId1234,
                '1,2,3,4',
                array('class' => 'TestClass'),
                'expectedCalls' => array(
                    'managerRegistry' => array(
                        array('getManagerForClass', array('TestClass'), array('self', 'getMockEntityManager')),
                    ),
                    'entitiesToIdsTransformer' => array(
                        array('transform', array(null), array()),
                        array('reverseTransform', array(array(1, 2, 3, 4)), $entitiesId1234),
                        array('transform', array($entitiesId1234), array(1, 2, 3, 4)),
                    )
                )
            ),
            'accept array' => array(
                array(1, 2, 3, 4),
                $entitiesId1234,
                '1,2,3,4',
                array('class' => 'TestClass'),
                'expectedCalls' => array(
                    'managerRegistry' => array(
                        array('getManagerForClass', array('TestClass'), array('self', 'getMockEntityManager')),
                    ),
                    'entitiesToIdsTransformer' => array(
                        array('transform', array(null), array()),
                        array('reverseTransform', array(array(1, 2, 3, 4)), $entitiesId1234),
                        array('transform', array($entitiesId1234), array(1, 2, 3, 4)),
                    )
                )
            ),
            'custom entity manager name' => array(
                '1,2,3,4',
                $entitiesId1234,
                '1,2,3,4',
                array('class' => 'TestClass', 'em' => 'custom_entity_manager'),
                'expectedCalls' => array(
                    'managerRegistry' => array(
                        array('getManager', array('custom_entity_manager'), array('self', 'getMockEntityManager')),
                    ),
                    'entitiesToIdsTransformer' => array(
                        array('transform', array(null), array()),
                        array('reverseTransform', array(array(1, 2, 3, 4)), $entitiesId1234),
                        array('transform', array($entitiesId1234), array(1, 2, 3, 4)),
                    )
                )
            ),
            'custom entity manager object' => array(
                '1,2,3,4',
                $entitiesId1234,
                '1,2,3,4',
                array('class' => 'TestClass', 'em' => array('self', 'getMockEntityManager')),
                'expectedCalls' => array(
                    'managerRegistry' => array(),
                    'entitiesToIdsTransformer' => array(
                        array('transform', array(null), array()),
                        array('reverseTransform', array(array(1, 2, 3, 4)), $entitiesId1234),
                        array('transform', array($entitiesId1234), array(1, 2, 3, 4)),
                    )
                )
            ),
            'custom query builder callback' => array(
                '1,2,3,4',
                $entitiesId1234,
                '1,2,3,4',
                array(
                    'class' => 'TestClass',
                    'queryBuilder' => function ($repository, array $ids) use ($self) {
                        $result = $repository->createQueryBuilder('o');
                        $result->where('o.id IN (:values)')->setParameter('values', $ids);
                        return $result;
                    }
                ),
                'expectedCalls' => array(
                    'managerRegistry' => array(
                        array('getManagerForClass', array('TestClass'), array('self', 'getMockEntityManager')),
                    ),
                    'entitiesToIdsTransformer' => array(
                        array('transform', array(null), array()),
                        array('reverseTransform', array(array(1, 2, 3, 4)), $entitiesId1234),
                        array('transform', array($entitiesId1234), array(1, 2, 3, 4)),
                    )
                )
            ),
        );
    }

    /**
     * @dataProvider createErrorsDataProvider
     * @param array $options
     * @param array $expectedCalls
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     */
    public function testCreateErrors(
        array $options,
        array $expectedCalls,
        $expectedException,
        $expectedExceptionMessage
    ) {
        foreach ($expectedCalls as $key => $calls) {
            $this->addMockExpectedCalls($key, $calls);
        }

        $this->setExpectedException($expectedException, $expectedExceptionMessage);
        $this->factory->create($this->getTestFormType(), null, $options);
    }

    /**
     * Data provider for testBindData
     *
     * @return array
     */
    public function createErrorsDataProvider()
    {
        return array(
            'cannot resolve entity manager by class' => array(
                array('class' => 'TestClass'),
                'expectedCalls' => array(
                    'managerRegistry' => array(
                        array('getManagerForClass', array('TestClass'), null),
                    )
                ),
                'expectedException' => 'Symfony\Component\Form\Exception\FormException',
                'expectedExceptionMessage'
                    => 'Class "TestClass" is not a managed Doctrine entity. Did you forget to map it?'
            ),
            'cannot resolve entity manager by name' => array(
                array('class' => 'TestClass', 'em' => 'custom_entity_manager'),
                'expectedCalls' => array(
                    'managerRegistry' => array(
                        array('getManager', array('custom_entity_manager'), null),
                    )
                ),
                'expectedException' => 'Symfony\Component\Form\Exception\FormException',
                'expectedExceptionMessage'
                    => 'Class "TestClass" is not a managed Doctrine entity. Did you forget to map it?'
            ),
            'invalid em' => array(
                array('class' => 'TestClass', 'em' => new \stdClass()),
                'expectedCalls' => array(
                    'managerRegistry' => array()
                ),
                'expectedException' => 'Symfony\Component\Form\Exception\FormException',
                'expectedExceptionMessage'
                    => 'Option "em" should be a string or entity manager object, stdClass given'
            ),
            'invalid queryBuilder' => array(
                array('class' => 'TestClass', 'queryBuilder' => 'invalid'),
                'expectedCalls' => array(
                    'managerRegistry' => array(
                        array('getManagerForClass', array('TestClass'), array('self', 'getMockEntityManager')),
                    ),
                ),
                'expectedException' => 'Symfony\Component\Form\Exception\FormException',
                'expectedExceptionMessage'
                    => 'Option "queryBuilder" should be a callable, string given'
            ),
        );
    }

    public function testCreateEntitiesToIdsTransformer()
    {
        $options = array(
            'em' => $this->getMockEntityManager(),
            'class' => 'TestClass',
            'property' => 'id',
            'queryBuilder' => function ($repository, array $ids) {
                return $repository->createQueryBuilder('o')->where('o.id IN (:values)')->setParameter('values', $ids);
            },
            'values_delimiter' => ','
        );
        $builder = $this->getMockBuilder('Symfony\Component\Form\Tests\FormBuilderInterface')
            ->setMethods(array('addViewTransformer', 'addEventSubscriber'))
            ->getMockForAbstractClass();

        $builder->expects($this->at(0))
            ->method('addViewTransformer')
            ->with(
                $this->callback(
                    function ($transformer) use ($options) {
                        \PHPUnit_Framework_TestCase::assertInstanceOf(
                            'Oro\Bundle\FormBundle\Form\DataTransformer\EntitiesToIdsTransformer',
                            $transformer
                        );
                        \PHPUnit_Framework_TestCase::assertAttributeEquals(
                            $options['em'],
                            'em',
                            $transformer
                        );
                        \PHPUnit_Framework_TestCase::assertAttributeEquals(
                            $options['class'],
                            'className',
                            $transformer
                        );
                        \PHPUnit_Framework_TestCase::assertAttributeEquals(
                            $options['property'],
                            'property',
                            $transformer
                        );
                        \PHPUnit_Framework_TestCase::assertAttributeEquals(
                            $options['queryBuilder'],
                            'queryBuilderCallback',
                            $transformer
                        );
                        return true;
                    }
                )
            )
            ->will($this->returnSelf());

        $builder->expects($this->at(1))
            ->method('addViewTransformer')
            ->will($this->returnSelf());

        $this->type = new EntityIdentifierType($this->getMockManagerRegistry());
        $this->type->buildForm($builder, $options);
    }

    /**
     * Create list of mocked entities by id property name and values
     *
     * @param string $property
     * @param array $values
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
     * @param string $property
     * @param mixed $value
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
     * @param \PHPUnit_Framework_MockObject_MockObject|string $mock
     * @param array $expectedCalls
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
     * @return ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockManagerRegistry()
    {
        if (!$this->managerRegistry) {
            $this->managerRegistry = $this->getMockForAbstractClass('Doctrine\Common\Persistence\ManagerRegistry');
        }

        return $this->managerRegistry;
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
     * @return EntitiesToIdsTransformer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockEntitiesToIdsTransformer()
    {
        if (!$this->entitiesToIdsTransformer) {
            $this->entitiesToIdsTransformer =
                $this->getMockBuilder('Oro\Bundle\FormBundle\Form\DataTransformer\EntitiesToIdsTransformer')
                    ->disableOriginalConstructor()
                    ->setMethods(array('transform', 'reverseTransform'))
                    ->getMockForAbstractClass();
        }
        return $this->entitiesToIdsTransformer;
    }
}
