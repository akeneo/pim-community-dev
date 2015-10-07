<?php

namespace Oro\Bundle\TranslationBundle\Tests\Unit\Form\Type;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Configuration;
use Gedmo\Translatable\Query\TreeWalker\TranslationWalker;

use Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType;
use Oro\Bundle\TranslationBundle\Tests\Unit\Form\Type\Stub\TestEntity;

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
     * @var Configuration
     */
    protected $ormConfiguration;

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

        $this->ormConfiguration = $this->getMockBuilder('Doctrine\ORM\Configuration')
            ->disableOriginalConstructor()
            ->setMethods(array('addCustomHydrationMode'))
            ->getMock();

        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getClassMetadata', 'getConfiguration'))
            ->getMock();
        $this->entityManager->expects($this->any())
            ->method('getClassMetadata')
            ->with(self::TEST_CLASS)
            ->will($this->returnValue($this->classMetadata));
        $this->entityManager->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue($this->ormConfiguration));

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

    protected function tearDown()
    {
        unset($this->classMetadata);
        unset($this->ormConfiguration);
        unset($this->entityManager);
        unset($this->registry);
        unset($this->entityRepository);
        unset($this->queryBuilder);
        unset($this->type);
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
     * @param  array $choices
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
     * @param array $expectedCalls
     *
     * @dataProvider buildFormDataProvider
     */
    public function testBuildForm($options, array $expectedCalls = array())
    {
        $formBuilder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('addEventSubscriber', 'addViewTransformer'))
            ->getMock();

        foreach ($expectedCalls as $method => $parameters) {
            $mocker = $formBuilder->expects($this->exactly($parameters['count']))
                ->method($method)
                ->will($this->returnSelf());
            call_user_func_array(array($mocker, 'with'), $parameters['with']);
        }

        // test
        $this->type->buildForm($formBuilder, $options);
    }

    /**
     * @return array
     */
    public function buildFormDataProvider()
    {
        return array(
            'single' => array(
                'options' => array(
                    'class'    => self::TEST_CLASS,
                    'multiple' => false,
                ),
            ),
            'multiple' => array(
                'options'       => array(
                    'class'    => self::TEST_CLASS,
                    'multiple' => true,
                ),
                'expectedCalls' => array(
                    'addEventSubscriber' => array(
                        'count' => 1,
                        'with'  => array(
                            $this->isInstanceOf(
                                'Symfony\Bridge\Doctrine\Form\EventListener\MergeDoctrineCollectionListener'
                            )
                        )
                    ),
                    'addViewTransformer' => array(
                        'count' => 1,
                        'with'  => array(
                            $this->isInstanceOf(
                                'Oro\Bundle\TranslationBundle\Form\DataTransformer\CollectionToArrayTransformer'
                            ),
                            true
                        )
                    ),
                ),
            ),
        );
    }

    /**
     * @param array $choiceListOptions
     * @param array $expectedChoices
     * @param boolean $expectTranslation
     *
     * @dataProvider configureOptionsDataProvider
     */
    public function testSetDefaultOptions(array $choiceListOptions, array $expectedChoices, $expectTranslation = false)
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

        // expectation for translation hydrator and hint
        if ($expectTranslation) {
            /** @var $configuration \PHPUnit_Framework_MockObject_MockObject */
            $configuration = $this->ormConfiguration;
            $configuration->expects($this->once())
                ->method('addCustomHydrationMode')
                ->with(
                    TranslationWalker::HYDRATE_OBJECT_TRANSLATION,
                    'Gedmo\\Translatable\\Hydrator\\ORM\\ObjectHydrator'
                );

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
        $this->type->configureOptions($optionsResolver);
    }

    /**
     * @param  array                 $options
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
    public function configureOptionsDataProvider()
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
                'expectTranslation' => true,
            ),
            'query_builder' => array(
               'choiceListOptions' => array(
                   'class'    => self::TEST_CLASS,
                   'property' => self::TEST_PROPERTY,
                   'choices'  => null,
                   'query_builder' => 'object'
                ),
                'expectedChoices' => $testChoiceEntities,
                'expectTranslation' => true,
            ),
            'query_builder_callback' => array(
                'choiceListOptions' => array(
                    'class'    => self::TEST_CLASS,
                    'property' => self::TEST_PROPERTY,
                    'choices'  => null,
                    'query_builder' => 'closure'
                ),
                'expectedChoices' => $testChoiceEntities,
                'expectTranslation' => true,
            ),
        );
    }

    /**
     * @param callback $choiceList
     * @param array    $options
     * @param array    $expectedChoices
     */
    public function assertChoiceList($choiceList, $options, $expectedChoices)
    {
        /** @var $objectChoiceList ObjectChoiceList */
        $objectChoiceList = $choiceList($this->getResolverOptions($options));

        $this->assertInstanceOf('Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList', $objectChoiceList);
        $this->assertEquals($expectedChoices, $objectChoiceList->getChoices());
    }

    /**
     * @param  array   $options
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
