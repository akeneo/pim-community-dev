<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Form\Type;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Form\Tests\FormIntegrationTestCase;

use Oro\Bundle\FormBundle\Autocomplete\SearchRegistry;
use Oro\Bundle\FormBundle\Autocomplete\SearchHandler;
use Oro\Bundle\FormBundle\Autocomplete\SearchHandlerInterface;
use Oro\Bundle\FormBundle\Autocomplete\ConverterInterface;

use Oro\Bundle\FormBundle\Form\DataTransformer\EntityToIdTransformer;
use Oro\Bundle\FormBundle\Form\Type\OroJquerySelect2HiddenType;

use Oro\Bundle\FormBundle\Tests\Unit\MockHelper;

class OroJquerySelect2HiddenTypeTest extends FormIntegrationTestCase
{
    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $factory;

    /**
     * @var OroJquerySelect2HiddenType
     */
    private $type;

    /**
     * @var SearchRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchRegistry;

    /**
     * @var SearchHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchHandler;

    /**
     * @var ConverterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $converter;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManager;

    /**
     * @var EntityToIdTransformer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityToIdTransformer;

    protected function setUp()
    {
        parent::setUp();
        $this->type = $this->getMockBuilder('Oro\Bundle\FormBundle\Form\Type\OroJquerySelect2HiddenType')
            ->setMethods(array('createDefaultTransformer'))
            ->setConstructorArgs(array($this->getMockEntityManager(), $this->getMockSearchRegistry()))
            ->getMock();
        /*$this->type->expects($this->any())->method('createDefaultTransformer')
            ->will($this->returnValue($this->getMockEntityToIdTransformer()));*/
    }

    protected function getExtensions()
    {
        return array_merge(parent::getExtensions(), array(new TestFormExtension()));
    }

    /**
     * @dataProvider bindDataProvider
     * @param mixed $bindData
     * @param mixed $formData
     * @param mixed $viewData
     * @param array $options
     * @param array $expectedCalls
     * @param array $expectedVars
     */
    public function testBindData(
        $bindData,
        $formData,
        $viewData,
        array $options,
        array $expectedCalls,
        array $expectedVars
    ) {
        if (isset($options['converter'])
            && is_string($options['converter'])
            && method_exists($this, $options['converter'])
        ) {
            $options['converter'] = $this->$options['converter']();
        }

        foreach ($expectedCalls as $key => $calls) {
            $mock = $this->{'getMock' . ucfirst($key)}();
            MockHelper::addMockExpectedCalls($mock, $calls, $this);
        }

        $form = $this->factory->create($this->type, null, $options);

        $form->bind($bindData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($formData, $form->getData());

        $view = $form->createView();
        $this->assertEquals($viewData, $view->vars['value']);

        foreach ($expectedVars as $name => $expectedValue) {
            $this->assertEquals($expectedValue, $view->vars[$name]);
        }
    }

    /**
     * Data provider for testBindData
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function bindDataProvider()
    {
        $entityId1 = $this->createMockEntity('id', 1);

        return array(
            'use autocomplete_alias' => array(
                '1',
                $entityId1,
                '1',
                array('autocomplete_alias' => 'foo'),
                'expectedCalls' => array(
                    'searchRegistry' => array(
                        array('getSearchHandler', array('foo'), 'getMockSearchHandler'),
                        array('getSearchHandler', array('foo'), 'getMockSearchHandler'),
                        array('getSearchHandler', array('foo'), 'getMockSearchHandler')
                    ),
                    'searchHandler' => array(
                        array('getProperties', array(), array('bar', 'baz')),
                        array('getEntityName', array(), 'TestEntityClass'),
                        array(
                            'convertItem',
                            array($entityId1),
                            array('id' => 1, 'bar' => 'Bar value', 'baz' => 'Baz value')
                        ),
                    ),
                    'formType' => array(
                        array('createDefaultTransformer', array('TestEntityClass'), 'getMockEntityToIdTransformer')
                    ),
                    'entityToIdTransformer' => array(
                        array('transform', array(null), null),
                        array('reverseTransform', array('1'), $entityId1)
                    )
                ),
                'expectedVars' => array(
                    'configs' => array(
                        'placeholder' => 'oro.form.choose_value',
                        'allowClear' => 1,
                        'minimumInputLength' => 1,
                        'autocomplete_alias' => 'foo',
                        'properties' => array('bar', 'baz'),
                        'route_name' => 'oro_form_autocomplete_search',
                        'extra_config' => 'autocomplete'
                    ),
                    'attr' => array(
                        'data-entity' => json_encode(array('id' => 1, 'bar' => 'Bar value', 'baz' => 'Baz value'))
                    )
                )
            ),
            'without autocomplete_alias' => array(
                '1',
                $entityId1,
                '1',
                array(
                    'configs' => array(
                        'route_name' => 'custom_route'
                    ),
                    'converter' => 'getMockConverter',
                    'entity_class' => 'TestEntityClass'
                ),
                'expectedCalls' => array(
                    'converter' => array(
                        array(
                            'convertItem',
                            array($entityId1),
                            array('id' => 1, 'bar' => 'Bar value', 'baz' => 'Baz value')
                        ),
                    ),
                    'formType' => array(
                        array('createDefaultTransformer', array('TestEntityClass'), 'getMockEntityToIdTransformer')
                    ),
                    'entityToIdTransformer' => array(
                        array('transform', array(null), null),
                        array('reverseTransform', array('1'), $entityId1)
                    )
                ),
                'expectedVars' => array(
                    'configs' => array(
                        'placeholder' => 'oro.form.choose_value',
                        'allowClear' => 1,
                        'minimumInputLength' => 1,
                        'route_name' => 'custom_route'
                    ),
                    'attr' => array(
                        'data-entity' => json_encode(array('id' => 1, 'bar' => 'Bar value', 'baz' => 'Baz value'))
                    )
                )
            )
        );
    }

    /**
     * @dataProvider createErrorsDataProvider
     * @param array  $options
     * @param array  $expectedCalls
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     */
    public function testCreateErrors(
        array $options,
        array $expectedCalls,
        $expectedException,
        $expectedExceptionMessage
    ) {
        if (isset($options['converter'])
            && is_string($options['converter'])
            && method_exists($this, $options['converter'])
        ) {
            $options['converter'] = $this->$options['converter']();
        }

        foreach ($expectedCalls as $key => $calls) {
            $mock = $this->{'getMock' . ucfirst($key)}();
            MockHelper::addMockExpectedCalls($mock, $calls, $this);
        }

        $this->setExpectedException($expectedException, $expectedExceptionMessage);
        $this->factory->create($this->type, null, $options);
    }

    /**
     * Data provider for testBindData
     *
     * @return array
     */
    public function createErrorsDataProvider()
    {
        return array(
            'configs.route_name or configs.ajax.url must be set' => array(
                array(),
                'expectedCalls' => array(),
                'expectedException' => 'Symfony\Component\Form\Exception\InvalidConfigurationException',
                'expectedExceptionMessage' => 'Either option "configs.route_name" or "configs.ajax.url" must be set.'
            ),
            'converter must be set' => array(
                array(
                    'configs' => array(
                        'route_name' => 'foo'
                    )
                ),
                'expectedCalls' => array(),
                'expectedException' => 'Symfony\Component\Form\Exception\InvalidConfigurationException',
                'expectedExceptionMessage' => 'The option "converter" must be set.'
            ),
            'converter invalid' => array(
                array(
                    'converter' => 'bar',
                    'configs' => array(
                        'route_name' => 'foo'
                    )
                ),
                'expectedCalls' => array(),
                'expectedException' => 'Symfony\Component\Form\Exception\UnexpectedTypeException',
                'expectedExceptionMessage' => 'Expected argument of type "Oro\Bundle\FormBundle\Autocomplete\ConverterInterface", "string" given'
            ),
            'entity_class must be set' => array(
                array(
                    'converter' => 'getMockConverter',
                    'configs' => array(
                        'route_name' => 'foo'
                    )
                ),
                'expectedCalls' => array(),
                'expectedException' => 'Symfony\Component\Form\Exception\InvalidConfigurationException',
                'expectedExceptionMessage' => 'The option "entity_class" must be set.'
            ),
            'entity_class must be set2' => array(
                array(
                    'converter' => 'getMockConverter',
                    'entity_class' => 'bar',
                    'configs' => array(
                        'route_name' => 'foo'
                    ),
                    'transformer' => 'invalid'
                ),
                'expectedCalls' => array(),
                'expectedException' => 'Symfony\Component\Form\Exception\TransformationFailedException',
                'expectedExceptionMessage' =>
                    sprintf(
                        'The option "transformer" must be an instance of "%s".',
                        'Symfony\Component\Form\DataTransformerInterface'
                    )
            )
        );
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
    public function getMockEntityManager()
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
     * @return SearchRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockSearchRegistry()
    {
        if (!$this->searchRegistry) {
            $this->searchRegistry = $this->getMockBuilder('Oro\Bundle\FormBundle\Autocomplete\SearchRegistry')
                ->disableOriginalConstructor()
                ->setMethods(array('hasSearchHandler', 'getSearchHandler'))
                ->getMock();
        }

        return $this->searchRegistry;
    }

    /**
     * @return ConverterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockConverter()
    {
        if (!$this->converter) {
            $this->converter = $this->getMock('Oro\Bundle\FormBundle\Autocomplete\ConverterInterface');
        }

        return $this->converter;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockFormType()
    {
        return $this->type;
    }

    /**
     * @return SearchHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockSearchHandler()
    {
        if (!$this->searchHandler) {
            $this->searchHandler = $this->getMock('Oro\Bundle\FormBundle\Autocomplete\SearchHandlerInterface');
        }

        return $this->searchHandler;
    }

    /**
     * @return EntityToIdTransformer|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockEntityToIdTransformer()
    {
        if (!$this->entityToIdTransformer) {
            $this->entityToIdTransformer =
                $this->getMockBuilder('Oro\Bundle\FormBundle\Form\DataTransformer\EntityToIdTransformer')
                    ->disableOriginalConstructor()
                    ->setMethods(array('transform', 'reverseTransform'))
                    ->getMockForAbstractClass();
        }

        return $this->entityToIdTransformer;
    }
}
