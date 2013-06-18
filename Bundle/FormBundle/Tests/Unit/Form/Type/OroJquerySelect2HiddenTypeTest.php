<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Form\Type;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;

use Symfony\Component\Form\Tests\FormIntegrationTestCase;

use Oro\Bundle\FormBundle\Autocomplete\SearchRegistry;
use Oro\Bundle\FormBundle\Autocomplete\SearchHandlerInterface;

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
        $this->type->expects($this->any())->method('createDefaultTransformer')
            ->will($this->returnValue($this->getMockEntityToIdTransformer()));
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
            'default' => array(
                '1',
                $entityId1,
                '1',
                array('autocomplete_alias' => 'foo'),
                'expectedCalls' => array(
                    'searchRegistry' => array(
                        array('hasSearchHandler', array('foo'), true),
                        array('getSearchHandler', array('foo'), 'getMockSearchHandler')
                    ),
                    'searchHandler' => array(
                        array('getProperties', array(), array('bar', 'baz')),
                        array(
                            'convertItem',
                            array($entityId1),
                            array('id' => 1, 'bar' => 'Bar value', 'baz' => 'Baz value')
                        ),
                    ),
                    'entityToIdTransformer' => array(
                        array('transform', array(null), null),
                        array('reverseTransform', array('1'), $entityId1)
                    )
                ),
                'expectedVars' => array(
                    'configs' => array(
                        'extra_config' => 'autocomplete',
                        'properties' => array('bar', 'baz'),
                        'selection_template_twig' => null,
                        'result_template_twig' => null,
                        'placeholder' => 'Choose a value...',
                        'allowClear' => 1,
                        'minimumInputLength' => 1,
                        'ajax' => array('url' => null),
                        'autocomplete_alias' => 'foo',
                        'route_name' => 'oro_form_autocomplete_search'
                    ),
                    'attr' => array(
                        'data-entity' => json_encode(array('id' => 1, 'bar' => 'Bar value', 'baz' => 'Baz value'))
                    )
                )
            ),
            'custom' => array(
                '1',
                $entityId1,
                '1',
                array(
                    'autocomplete_alias' => 'foo',
                    'url' => 'custom_url',
                    'route_name' => 'custom_route',
                    'configs' => array(
                        'extra_config' => 'custom_extra_config',
                        'selection_template_twig' => 'custom_selection_template_twig',
                        'result_template_twig' => 'custom_result_template_twig',
                        'placeholder' => 'custom_placeholder',
                        'allowClear' => false,
                        'minimumInputLength' => 2,
                        'ajax' => array('custom_property' => 'custom_value'),
                        'custom_property' => 'custom_value'
                    )
                ),
                'expectedCalls' => array(
                    'searchRegistry' => array(
                        array('hasSearchHandler', array('foo'), true),
                        array('getSearchHandler', array('foo'), 'getMockSearchHandler')
                    ),
                    'searchHandler' => array(
                        array('getProperties', array(), array('bar', 'baz')),
                        array(
                            'convertItem',
                            array($entityId1),
                            array('id' => 1, 'bar' => 'Bar value', 'baz' => 'Baz value')
                        ),
                    ),
                    'entityToIdTransformer' => array(
                        array('transform', array(null), null),
                        array('reverseTransform', array('1'), $entityId1)
                    )
                ),
                'expectedVars' => array(
                    'configs' => array(
                        'extra_config' => 'custom_extra_config',
                        'properties' => array('bar', 'baz'),
                        'selection_template_twig' => 'custom_selection_template_twig',
                        'result_template_twig' => 'custom_result_template_twig',
                        'placeholder' => 'custom_placeholder',
                        'allowClear' => false,
                        'minimumInputLength' => 2,
                        'ajax' => array('url' => 'custom_url', 'custom_property' => 'custom_value'),
                        'autocomplete_alias' => 'foo',
                        'route_name' => 'custom_route',
                        'custom_property' => 'custom_value'
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
            'autocomplete_alias is required' => array(
                array(),
                'expectedCalls' => array(),
                'expectedException' => 'Symfony\Component\OptionsResolver\Exception\MissingOptionsException',
                'expectedExceptionMessage' => 'The required option "autocomplete_alias" is  missing.'
            ),
            'cannot normalize search_handler when associated handler not found in registry' => array(
                array('autocomplete_alias' => 'foo'),
                'expectedCalls' => array(
                    'searchRegistry' => array(
                        array('hasSearchHandler', array('foo'), false),
                    )
                ),
                'expectedException' => 'Symfony\Component\Form\Exception\FormException',
                'expectedExceptionMessage' => 'The option "autocomplete_alias" references to not '
                    . 'registered autocomplete search handler "foo".'
            ),
            'cannot normalize search_handler when it has incorrect type' => array(
                array('autocomplete_alias' => 'foo', 'search_handler' => 'invalid'),
                'expectedCalls' => array(),
                'expectedException' => 'Symfony\Component\Form\Exception\FormException',
                'expectedExceptionMessage' => 'The option "search_handler" must be an instance of '
                    . '"Oro\Bundle\FormBundle\Autocomplete\SearchHandlerInterface".'
            ),
            'cannot normalize transformer when it has incorrect type' => array(
                array('autocomplete_alias' => 'foo', 'transformer' => 'invalid'),
                'expectedCalls' => array(
                    'searchRegistry' => array(
                        array('hasSearchHandler', array('foo'), true),
                        array('getSearchHandler', array('foo'), 'getMockSearchHandler'),
                    )
                ),
                'expectedException' => 'Symfony\Component\Form\Exception\FormException',
                'expectedExceptionMessage' => 'The option "transformer" must be an instance of '
                    . '"Symfony\Component\Form\DataTransformerInterface".'
            )
        );
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
    protected function getMockEntityToIdTransformer()
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
