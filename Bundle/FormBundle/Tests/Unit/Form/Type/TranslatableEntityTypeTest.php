<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\TranslatableEntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Oro\Bundle\FormBundle\Tests\Unit\Form\Type\Stub\TestEntity;

class TranslatableEntityTypeTest extends \PHPUnit_Framework_TestCase
{
    const TEST_CLASS      = 'TestClass';
    const TEST_IDENTIFIER = 'testId';
    const TEST_PROPERTY   = 'testProperty';

    /**
     * @var ClassMetadataInfo
     */
    protected $classMetadata;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var EntityRepository
     */
    protected $entityRepository;

    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var TranslatableEntityType
     */
    protected $type;

    /**
     * @var array
     */
    protected $testChoices = array('one', 'two', 'three');

    protected function setUp()
    {
        $this->classMetadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadataInfo')
            ->disableOriginalConstructor()
            ->setMethods(array('getSingleIdentifierFieldName'))
            ->getMock();
        $this->classMetadata->expects($this->any())
            ->method('getSingleIdentifierFieldName')
            ->will($this->returnValue(self::TEST_IDENTIFIER));

        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getClassMetadata'))
            ->getMock();
        $this->entityManager->expects($this->any())
            ->method('getClassMetadata')
            ->with(self::TEST_CLASS)
            ->will($this->returnValue($this->classMetadata));

        $this->registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->setMethods('getManager', 'getRepository')
            ->getMockForAbstractClass();
        $this->registry->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->entityManager));
        $this->registry->expects($this->any())
            ->method('getRepository')
            ->with(self::TEST_CLASS)
            ->will($this->returnValue($this->getEntityRepository()));

        $this->type = new TranslatableEntityType($this->registry);
    }

    public function testGetName()
    {
        $this->assertEquals(TranslatableEntityType::NAME, $this->type->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals('choice', $this->type->getParent());
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        $testChoiceEntities = $this->getTestChoiceEntities($this->testChoices);

        if (!$this->queryBuilder) {
            $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
                ->disableOriginalConstructor()
                ->setMethods(array('execute', 'setHint'))
                ->getMockForAbstractClass();
            $query->expects($this->any())
                ->method('execute')
                ->will($this->returnValue($testChoiceEntities));

            $this->queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
                ->disableOriginalConstructor()
                ->setMethods(array('getQuery'))
                ->getMock();
            $this->queryBuilder->expects($this->any())
                ->method('getQuery')
                ->will($this->returnValue($query));
        }

        return $this->queryBuilder;
    }

    /**
     * @return EntityRepository
     */
    public function getEntityRepository()
    {
        if (!$this->entityRepository) {
            $this->entityRepository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
                ->disableOriginalConstructor()
                ->getMock();
            $this->entityRepository->expects($this->any())
                ->method('createQueryBuilder')
                ->with('e')
                ->will($this->returnValue($this->getQueryBuilder()));
        }

        return $this->entityRepository;
    }

    /**
     * @param array $choices
     * @return array
     */
    protected function getTestChoiceEntities($choices)
    {
        foreach ($choices as $key => $value) {
            $entity = new TestEntity($key, $value);
            $choices[$key] = $entity;
        }

        return $choices;
    }

    /**
     * @param array $options
     * @param string $transformerClass
     *
     * @dataProvider buildFormDataProvider
     */
    public function testBuildForm($options, $transformerClass)
    {
        // mock
        $formBuilder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('resetViewTransformers'))
            ->getMock();
        $formBuilder->expects($this->at(0))
            ->method('resetViewTransformers');

        // test
        $this->type->buildForm($formBuilder, $options);

        // assertions
        /** @var $formBuilder FormBuilderInterface */
        $transformers = $formBuilder->getViewTransformers();
        $this->assertCount(1, $transformers);

        $transformer = current($transformers);
        $this->assertInstanceOf($transformerClass, $transformer);

        $this->assertAttributeEquals($this->entityManager, 'em', $transformer);
        $this->assertAttributeEquals(self::TEST_CLASS, 'className', $transformer);
        $this->assertAttributeEquals(self::TEST_IDENTIFIER, 'property', $transformer);
    }

    /**
     * @return array
     */
    public function buildFormDataProvider()
    {
        return array(
            'single' => array(
                'options'          => array('class' => self::TEST_CLASS),
                'transformerClass' => 'Oro\Bundle\FormBundle\Form\DataTransformer\EntityToIdTransformer'
            ),
            'multiple' => array(
                'options'          => array('class' => self::TEST_CLASS, 'multiple' => true),
                'transformerClass' => 'Oro\Bundle\FormBundle\Form\DataTransformer\EntitiesToIdsTransformer'
            ),
        );
    }

    /**
     * @param array $choiceListOptions
     * @param array $expectedChoices
     * @param boolean $expectSetHint
     *
     * @dataProvider setDefaultOptionsDataProvider
     */
    public function testSetDefaultOptions(array $choiceListOptions, array $expectedChoices, $expectSetHint = false)
    {
        $test = $this;

        // prepare query builder option
        if (isset($choiceListOptions['query_builder'])) {
            $choiceListOptions['query_builder'] = $this->getQueryBuilderOption($choiceListOptions);
        }

        // expectations for option resolver
        $expectedRequiredOptions = array('class');

        $optionsResolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolverInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('setRequired', 'setDefaults'))
            ->getMockForAbstractClass();
        $optionsResolver->expects($this->once())
            ->method('setRequired')
            ->with($expectedRequiredOptions);
        $optionsResolver->expects($this->once())
            ->method('setDefaults')
            ->will(
                $this->returnCallback(
                    function ($options) use ($test, $choiceListOptions, $expectedChoices) {
                        $test->assertNull($options['property']);
                        $test->assertNull($options['query_builder']);
                        $test->assertNull($options['choices']);
                        $test->assertInstanceOf('\Closure', $options['choice_list']);

                        $test->assertChoiceList($options['choice_list'], $choiceListOptions, $expectedChoices);
                    }
                )
            );

        // expectation for translation walker hint
        if ($expectSetHint) {
            /** @var $query \PHPUnit_Framework_MockObject_MockObject */
            $query = $this->getQueryBuilder()->getQuery();
            $query->expects($this->once())
                ->method('setHint')
                ->with(
                    Query::HINT_CUSTOM_OUTPUT_WALKER,
                    'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
                );
        }

        // test
        $this->type->setDefaultOptions($optionsResolver);
    }

    /**
     * @param array $options
     * @return callable|QueryBuilder
     */
    protected function getQueryBuilderOption(array $options)
    {
        if (empty($options['query_builder'])) {
            return null;
        }

        $test = $this;

        switch ($options['query_builder']) {
            case 'closure':
                return function (EntityRepository $entityRepository) use ($test) {
                    $test->assertEquals($test->getEntityRepository(), $entityRepository);
                    return $test->getQueryBuilder();
                };
            case 'object':
            default:
                return $this->getQueryBuilder();
        }
    }

    /**
     * @return array
     */
    public function setDefaultOptionsDataProvider()
    {
        $testChoiceEntities = $this->getTestChoiceEntities($this->testChoices);

        return array(
            'predefined_choices' => array(
                'choiceListOptions' => array(
                    'class'    => self::TEST_CLASS,
                    'property' => self::TEST_PROPERTY,
                    'choices'  => $testChoiceEntities
                 ),
                'expectedChoices' => $testChoiceEntities
            ),
            'all_choices' => array(
                'choiceListOptions' => array(
                    'class'    => self::TEST_CLASS,
                    'property' => self::TEST_PROPERTY,
                    'choices'  => null
                ),
                'expectedChoices' => $testChoiceEntities,
                'expectSetHint' => true,
            ),
            'query_builder' => array(
               'choiceListOptions' => array(
                   'class'    => self::TEST_CLASS,
                   'property' => self::TEST_PROPERTY,
                   'choices'  => null,
                   'query_builder' => 'object'
                ),
                'expectedChoices' => $testChoiceEntities,
                'expectSetHint' => true,
            ),
            'query_builder_callback' => array(
                'choiceListOptions' => array(
                    'class'    => self::TEST_CLASS,
                    'property' => self::TEST_PROPERTY,
                    'choices'  => null,
                    'query_builder' => 'closure'
                ),
                'expectedChoices' => $testChoiceEntities,
                'expectSetHint' => true,
            ),
        );
    }

    /**
     * @param callback $choiceList
     * @param array $options
     * @param array $expectedChoices
     */
    public function assertChoiceList($choiceList, $options, $expectedChoices)
    {
        /** @var $objectChoiceList ObjectChoiceList */
        $objectChoiceList = $choiceList($this->getResolverOptions($options));

        $this->assertInstanceOf('Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList', $objectChoiceList);
        $this->assertEquals($expectedChoices, $objectChoiceList->getChoices());
    }

    /**
     * @param array $options
     * @return Options
     */
    protected function getResolverOptions($options)
    {
        $resolverOptions = new Options();
        foreach ($options as $key => $value) {
            $resolverOptions->set($key, $value);
        }

        return $resolverOptions;
    }
}
