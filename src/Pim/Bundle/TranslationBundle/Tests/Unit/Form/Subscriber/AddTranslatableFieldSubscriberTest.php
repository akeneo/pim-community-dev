<?php

namespace Pim\Bundle\TranslationBundle\Form\Subscriber;

use Pim\Bundle\TranslationBundle\Form\Subscriber\AddTranslatableFieldSubscriber;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class AddTranslatableFieldSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Symfony\Component\Form\Form
     */
    protected $form;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->formFactory = $this->getFormFactoryMock();
        $this->form        = $this->getFormMock();
    }

    /**
     * Data provider with missing option
     *
     * @static
     *
     * @return array
     */
    public static function missingOptionDataProvider()
    {
        return array(
            'miss_translation_class' => array(
                array()
            ),
            'miss_entity_class' => array(
                array('translation_class' => 'Pim\\Bundle\\TranslationBundle\\Tests\\Entity\\ItemTranslation')
            ),
            'miss_field' => array(
                array(
                    'translation_class' => 'Pim\\Bundle\\TranslationBundle\\Tests\\Entity\\ItemTranslation',
                    'entity_class'      => 'Pim\\Bundle\\TranslationBundle\\Tests\\Entity\\Item'
                )
            ),
            'miss_default_locale' => array(
                array(
                    'translation_class' => 'Pim\\Bundle\\TranslationBundle\\Tests\\Entity\\ItemTranslation',
                    'entity_class'      => 'Pim\\Bundle\\TranslationBundle\\Tests\\Entity\\Item',
                    'field'             => 'name'
                )
            ),
            'miss_only_default' => array(
                array(
                    'translation_class' => 'Pim\\Bundle\\TranslationBundle\\Tests\\Entity\\ItemTranslation',
                    'entity_class'      => 'Pim\\Bundle\\TranslationBundle\\Tests\\Entity\\Item',
                    'field'             => 'name',
                    'default_locale'    => 'default'
                )
            ),
            'miss_locales' => array(
                array(
                    'translation_class' => 'Pim\\Bundle\\TranslationBundle\\Tests\\Entity\\ItemTranslation',
                    'entity_class'      => 'Pim\\Bundle\\TranslationBundle\\Tests\\Entity\\Item',
                    'field'             => 'name',
                    'default_locale'    => 'default',
                    'only_default'      => false
                )
            ),
            'miss_widget' => array(
                array(
                    'translation_class' => 'Pim\\Bundle\\TranslationBundle\\Tests\\Entity\\ItemTranslation',
                    'entity_class'      => 'Pim\\Bundle\\TranslationBundle\\Tests\\Entity\\Item',
                    'field'             => 'name',
                    'default_locale'    => 'default',
                    'only_default'      => false,
                    'locales'           => array('fr_FR', 'en_US')
                )
            ),
            'miss_required_locale' => array(
                array(
                    'translation_class' => 'Pim\\Bundle\\TranslationBundle\\Tests\\Entity\\ItemTranslation',
                    'entity_class'      => 'Pim\\Bundle\\TranslationBundle\\Tests\\Entity\\Item',
                    'field'             => 'name',
                    'default_locale'    => 'default',
                    'only_default'      => false,
                    'locales'           => array('fr_FR', 'en_US'),
                    'widget'            => 'text'
                )
            )
        );
    }

    /**
     * Test with missing option
     *
     * @param array $options
     *
     * @dataProvider missingOptionDataProvider
     * @expectedException \Pim\Bundle\TranslationBundle\Exception\MissingOptionException
     */
    public function testMissingOptionException(array $options)
    {
        $target             = $this->getTargetedClass($options);
        $translatableEntity = $this->getTranslatableEntityMock();
        $event              = $this->getEventMock($this->form, $translatableEntity, array());

        $target->preSetData($event);
        $target->bind($event);
        $target->postBind($event);
    }

    /**
     * Data provider for incorrect translation class
     *
     * @static
     *
     * @return array
     */
    public static function incorrectClassNameDataProvider()
    {
        return array(
            'incorrect_translation_class' => array(
                array(
                    'translation_class' => 'translation_class',
                    'entity_class'      => 'Pim\\Bundle\\TranslationBundle\\Tests\\Entity\\Item',
                    'field'             => 'name'
                )
            )
        );
    }

    /**
     * Test with incorrect translation and/or entity classes
     *
     * @param array $options
     *
     * @dataProvider incorrectClassNameDataProvider
     * @expectedException \ReflectionException
     * @expectedExceptionMessage Class translation_class does not exist
     */
    public function testReflectionException(array $options)
    {
        $target             = $this->getTargetedClass($options);
        $form               = $this->getFormMock();
        $translatableEntity = $this->getTranslatableEntityMock();
        $event              = $this->getEventMock($form, $translatableEntity, array());

        $target->preSetData($event);
    }

    /**
     * Data provider for getSubsbriberEvents method
     *
     * @static
     *
     * @return multitype:mixed
     */
    public static function getSubscriberEventsDataProvider()
    {
        return array(
            array(
                array(
                    'entity_class'      => 'Pim\\Bundle\\TranslationBundle\\Tests\\Entity\\Item',
                    'translation_class' => 'Pim\\Bundle\\TranslationBundle\\Tests\\Entity\\ItemTranslation',
                    'field'             => 'name'
                )
            )
        );
    }

    /**
     * Test subscriber events
     *
     * @param array $options
     *
     * @dataProvider getSubscriberEventsDataProvider
     */
    public function testGetSubscriberEvents(array $options)
    {
        $target = $this->getTargetedClass($options);
        $events = $target->getSubscribedEvents();

        $this->assertTrue(array_key_exists('form.pre_set_data', $events), 'preSetData');
        $this->assertTrue(array_key_exists('form.post_bind', $events), 'postBind');
        $this->assertTrue(array_key_exists('form.bind', $events), 'bind');
    }

    /**
     * Test preSet without data
     * It should do nothing
     *
     * @param array $options
     *
     * @dataProvider getSubscriberEventsDataProvider
     */
    public function testPreSetWithoutData(array $options)
    {
        $target = $this->getTargetedClass($options);
        $form   = $this->getFormMock();
        $event  = $this->getEventMock($form);

        $form->expects($this->never())
             ->method('getParent');

        $target->preSetData($event);
    }

    /**
     * Data provider for preSet data
     *
     * @static
     *
     * @return array
     */
    public static function preSetDataProvider()
    {
        return array(
            'not_only_default' => array(
                array(
                    'entity_class'      => 'Pim\\Bundle\\TranslationBundle\\Tests\\Entity\\Item',
                    'translation_class' => 'Pim\\Bundle\\TranslationBundle\\Tests\\Entity\\ItemTranslation',
                    'field'             => 'name',
                    'required_locale'   => array('default'),
                    'locales'           => array('en_US', 'fr_FR'),
                    'default_locale'    => 'default',
                    'only_default'      => false,
                    'widget'            => 'text'
                )
            ),
            'only_default' => array(
                array(
                    'entity_class'      => 'Pim\\Bundle\\TranslationBundle\\Tests\\Entity\\Item',
                    'translation_class' => 'Pim\\Bundle\\TranslationBundle\\Tests\\Entity\\ItemTranslation',
                    'field'             => 'name',
                    'required_locale'   => array('default'),
                    'locales'           => array('default', 'en_US', 'fr_FR'),
                    'default_locale'    => 'default',
                    'only_default'      => true,
                    'widget'            => 'text'
                )
            )
        );
    }

    /**
     * Test preSetData
     * It should add form fields for each translations
     *
     * @param array $options
     *
     * @dataProvider preSetDataProvider
     */
    public function testPreSetData(array $options)
    {
        $target                = $this->getTargetedClass($options);
        $translatableEntity    = $this->getTranslatableEntityMock();

        $event = $this->getEventMock($this->form, $translatableEntity, array());

        if ($options['only_default']) {
            $locales = array('default');
            $requiredLocales = array('default');
        } else {
            $locales = $options['locales'];
            $requiredLocales = $options['required_locale'];
        }

        foreach ($locales as $index => $locale) {
            $translation = $this->getTranslationMock($options['field'], $locale);

            $this->formFactory->expects($this->at($index))
                ->method('createNamed')
                ->with(
                    $locale,
                    $options['widget'],
                    '',
                    array(
                        'label'         => $locale,
                        'required'      => in_array($locale, $requiredLocales),
                        'mapped' => false,
                        'auto_initialize' => false
                    )
                )
                ->will($this->returnValue($field = $this->getFormMock()));

            $this->form->expects($this->any())
                 ->method('add')
                 ->with($this->equalTo($field));
        }

        $target->preSetData($event);
    }

    /**
     * Data provider for data binding
     *
     * @static
     *
     * @return array
     */
    public static function bindDataProvider()
    {
        return self::preSetDataProvider();
    }

    /**
     * Test data binding
     * It should validate required translations
     *
     * @param array $options
     *
     * @dataProvider bindDataProvider
     */
    public function testBindData(array $options)
    {
        $target                = $this->getTargetedClass($options);
        $translatableEntity    = $this->getTranslatableEntityMock();

        if ($options['only_default']) {
            $this->form->expects($this->at(0))
                 ->method('get')
                 ->with($options['default_locale'])
                 ->will($this->returnValue($defaultField = $this->getFormMock()));

            $defaultField->expects($this->any())
                         ->method('getData')
                         ->will($this->returnValue(null));
        } else {
            $locales = $options['locales'];

            foreach ($locales as $index => $locale) {
                $this->form->expects($this->at($index))
                     ->method('get')
                     ->with($locale)
                     ->will($this->returnValue($defaultField = $this->getFormMock()));

                $defaultField->expects($this->any())
                             ->method('getData')
                             ->will($this->returnValue(null));
            }
        }

        $event = $this->getEventMock($this->form, $translatableEntity, array('default'));

        $target->bind($event);
    }

    /**
     * Data provider post data binding
     * It should add translation after binding
     *
     * @static
     *
     * @return array
     */
    public static function postBindDataProvider()
    {
        return self::preSetDataProvider();
    }

    /**
     * Test data post binding
     * It should add translation if content is provided
     *
     * @param array $options
     *
     * @dataProvider postBindDataProvider
     */
    public function testPostBindData(array $options)
    {
        $this->markTestIncomplete('Not totally implemented');

        $target = $this->getTargetedClass($options);
        $translatableEntity = $this->getTranslatableEntityMock();

        $event = $this->getEventMock(
            $this->form,
            $translatableEntity
        );

        $target->postBind($event);
    }

    /**
     * Create tested subscriber
     *
     * @param array $options
     *
     * @return \Pim\Bundle\TranslationBundle\Form\Subscriber\AddTranslatableFieldSubscriber
     */
    protected function getTargetedClass(array $options)
    {
        return new AddTranslatableFieldSubscriber(
            $this->formFactory,
            $this->getValidatorMock(),
            $options
        );
    }

    /**
     * Create FormFactory mock
     *
     * @return Mock
     */
    protected function getFormFactoryMock()
    {
        $childForm = $this->getFormMock();

        $formFactory = $this->getMockBuilder('Symfony\Component\Form\FormFactory')
                            ->disableOriginalConstructor()
                            ->setMethods(array('createNamed'))
                            ->getMock();

        $formFactory->expects($this->any())
                    ->method('createNamed')
                    ->will($this->returnValue($childForm));

        return $formFactory;
    }

    /**
     * Create Validator mock
     *
     * @return Mock
     */
    protected function getValidatorMock()
    {
        return $this->getMock('Symfony\Component\Validator\ValidatorInterface');
    }

    /**
     * Create FormEvent mock
     *
     * @param Form  $form
     * @param array $parentData
     * @param array $data
     *
     * @return Mock
     */
    protected function getEventMock($form, $parentData = null, array $data = null)
    {
        $event = $this
            ->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->setMethods(array('getData', 'getForm'))
            ->getMock();

        $event->expects($this->any())
              ->method('getData')
              ->will($this->returnValue($data));

        $event->expects($this->any())
              ->method('getForm')
              ->will($this->returnValue($form));

        $parentForm = $this->getFormMock();

        $form->expects($this->any())
             ->method('getParent')
             ->will($this->returnValue($parentForm));

        $parentForm->expects($this->any())
                   ->method('getData')
                   ->will($this->returnValue($parentData));

        return $event;
    }

    /**
     * Create Form mock
     *
     * @return Mock
     */
    protected function getFormMock()
    {
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
                     ->disableOriginalConstructor()
                    ->setMethods(array('getParent', 'getData', 'add', 'get', 'addError'))
                    ->getMock();

        $form->expects($this->any())
             ->method('add')
             ->will($this->returnValue('Symfony\Component\Form\FormInterface'));

        if ($this->form) {
            $form->expects($this->any())
                 ->method('get')
                 ->will($this->returnValue($this->form));
        }

        return $form;
    }

    /**
     * Get translatable entity mock
     *
     * @return Mock
     */
    protected function getTranslatableEntityMock()
    {
        return $this->getMockForAbstractClass('Pim\Bundle\TranslationBundle\Tests\Entity\Item');
    }

    /**
     * Get translated entity mock
     *
     * @return Mock
     */
    protected function getTranslationMock()
    {
        return $this->getMockForAbstractClass('Pim\Bundle\TranslationBundle\Tests\Entity\ItemTranslation');
    }
}
