<?php

namespace Pim\Bundle\TranslationBundle\Form\Subscriber;

use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;
use Pim\Bundle\TranslationBundle\Form\Type\TranslatableFieldType;
use Symfony\Component\Form\Event\DataEvent;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\Forms;

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
     * Test subscriber events
     */
    public function testGetSubscriberEvents()
    {
        $target = $this->getTargetedClass();
        $events = $target->getSubscribedEvents();

        $this->assertTrue(array_key_exists('form.pre_set_data', $events), 'preSetData');
        $this->assertTrue(array_key_exists('form.post_bind', $events), 'postBind');
        $this->assertTrue(array_key_exists('form.bind', $events), 'bind');
    }

    /**
     * @test
     */
    public function itsPreSetDataShouldDoNothingIfDataIsNull()
    {
        $target = $this->getTargetedClass();
        $form   = $this->getFormMock();
        $event  = $this->getEventMock($form);

        $form->expects($this->never())
             ->method('getParent');

        $target->preSetData($event);
    }

    /**
     * @return multitype:multitype:string multitype:string
     */
    public static function preSetDataProvider()
    {
        return array(
            array(
                'requiredLocale'   => 'fr_FR',
                'locales'          => array('en_US', 'fr_FR'),
            ),
            array(
                'requiredLocale'   => 'en_US',
                'locales'          => array('fr_FR', 'en_US'),
            )
        );
    }

    /**
     * @param string $requiredLocale
     * @param array  $locales
     *
     * @test
     * @dataProvider preSetDataProvider
     */
    public function itsPreSetDataShouldAddFormFieldsForEachTranslations($requiredLocale, $locales)
    {
        $translationFactory    = $this->getTranslationFactoryMock();
        $formFactory           = $this->getFormFactoryMock();
        $target                = $this->getTargetedClass('name', 'text', $requiredLocale, $locales, $formFactory, $translationFactory);
        $form                  = $this->getFormMock();
        $translatableEntity    = $this->getTranslatableEntityMock();

        $event = $this->getEventMock($form, $translatableEntity, array());

        $translatableEntity->expects($this->once())
                           ->method('setTranslatableLocale')
                           ->with($this->equalTo('default'));

        foreach ($locales as $index => $locale) {
            $translation = $this->getTranslationMock('name', $locale);

            $translationFactory->expects($this->at($index))
                               ->method('createTranslation')
                               ->with($this->equalTo($locale))
                               ->will($this->returnValue($translation));

            $formFactory->expects($this->at($index))
                        ->method('createNamed')
                        ->with(
                            $this->equalTo(sprintf('name:%s', $locale)),
                            $this->equalTo('text'),
                            $this->equalTo(''),
                            $this->equalTo(
                                array(
                                    'label'         => $locale,
                                    'required'      => in_array($locale, array($requiredLocale)),
                                    'property_path' => false,
                                )
                            )
                        )
                        ->will($this->returnValue($field = $this->getFormMock()));

            $form->expects($this->any())
                 ->method('add')
                 ->with($this->equalTo($field));
        }

        $target->preSetData($event);
    }

    /**
     * @test
     */
    public function itShouldValidateRequiredTranslationsWhenBinding()
    {
        $translationFactory = $this->getTranslationFactoryMock();
        $target             = $this->getTargetedClass('name', 'text', 'fr_FR', array('fr_FR'), null, $translationFactory);
        $form               = $this->getFormMock();
        $event              = $this->getEventMock($form);

        $form->expects($this->at(0))
             ->method('get')
             ->with($this->equalTo('name:fr_FR'))
             ->will($this->returnValue($frField = $this->getFormMock()));

        $frField->expects($this->any())
                ->method('getData')
                ->will($this->returnValue(null));

        $form->expects($this->once())
             ->method('addError');

        $translationFactory->expects($this->once())
                           ->method('createTranslation')
                           ->with($this->equalTo('fr_FR'))
                           ->will($this->returnValue($frTranslation = $this->getTranslationMock('name', 'fr_FR')));

        $target->bind($event);
    }

    /**
     * @param string $requiredLocale
     * @param array  $locales
     *
     * @test
     * @dataProvider preSetDataProvider
     *
     */
    public function itShouldAddTranslationIfContentIsProvidedAfterBinding($requiredLocale, $locales)
    {
        $translationFactory = $this->getTranslationFactoryMock();
        $formFactory        = $this->getFormFactoryMock();
        $target             = $this->getTargetedClass('name', 'text', $requiredLocale, $locales, $formFactory, $translationFactory);
        $form               = $this->getFormMock();
        $translatableEntity = $this->getTranslatableEntityMock();
        $event              = $this->getEventMock($form, $translatableEntity, array());

        foreach ($locales as $index => $locale) {
            $form->expects($this->at($index+1))
                 ->method('get')
                 ->with(sprintf('name:%s', $locale))
                 ->will($this->returnValue($field = $this->getFormMock()));
            $field->expects($this->any())
                  ->method('getData')
                  ->will($this->returnValue('foo'));

            $translation = $this->getTranslationMock('name', $locale);

            $translationFactory->expects($this->at($index))
                               ->method('createTranslation')
                               ->with($this->equalTo($locale))
                               ->will($this->returnValue($translation));

            $translation->expects($this->once())
                        ->method('setContent')
                        ->with($this->equalTo('foo'));
        }

        $translatableEntity->expects($this->once())
                           ->method('setTranslatableLocale')
                           ->with($this->equalTo('default'));

        $target->postBind($event);
    }

    /**
     * @param string             $field
     * @param string             $widget
     * @param string             $requiredLocale
     * @param array              $locales
     * @param FormFactory        $formFactory
     * @param TranslationFactory $translationFactory
     *
     * @return \Pim\Bundle\TranslationBundle\Form\Subscriber\AddTranslatableFieldSubscriber
     */
    protected function getTargetedClass($field = null, $widget = null, $requiredLocale = null, array $locales = array(), $formFactory = null, $translationFactory = null)
    {
        return new AddTranslatableFieldSubscriber(
            $formFactory ?: $this->getFormFactoryMock(),
            $this->getValidatorMock(),
            $translationFactory ?: $this->getTranslationFactoryMock(),
            $field,
            $widget,
            $requiredLocale,
            $locales
        );
    }

    /**
     * @return Mock
     */
    protected function getFormFactoryMock()
    {
        return $this
            ->getMockBuilder('Symfony\Component\Form\FormFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('createNamed'))
            ->getMock();
    }

    /**
     * @return Mock
     */
    protected function getValidatorMock()
    {
        return $this->getMock('Symfony\Component\Validator\ValidatorInterface');
    }

    /**
     * @return Mock
     */
    protected function getTranslationFactoryMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\TranslationBundle\Factory\TranslationFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('createTranslation'))
            ->getMock();
    }

    /**
     * @param Form  $form
     * @param array $parentData
     * @param array $data
     *
     * @return Mock
     */
    protected function getEventMock($form, $parentData = null, array $data = null)
    {
        $event = $this
            ->getMockBuilder('Symfony\Component\Form\Event\DataEvent')
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
     * @return Mock
     */
    protected function getFormMock()
    {
        $config = $this
            ->getMockBuilder('Symfony\Component\Form\FormConfigBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('getCompound', 'getDataMapper', 'getEventDispatcher'))
            ->getMock();

        $config->expects($this->any())
               ->method('getCompound')
               ->will($this->returnValue(true));

        $config->expects($this->any())
               ->method('getDataMapper')
               ->will($this->returnValue($this->getDataMapperMock()));

        $config->expects($this->any())
               ->method('getEventDispatcher')
               ->will($this->returnValue($this->getEventDispatcherMock()));

        return $this
            ->getMockBuilder('Symfony\Component\Form\Form')
            ->setConstructorArgs(array($config))
            ->setMethods(array('getParent', 'getData', 'add', 'get', 'addError'))
            ->getMock();
    }

    /**
     * @return Mock
     */
    protected function getDataMapperMock()
    {
        return $this->getMock('Symfony\Component\Form\DataMapperInterface');
    }

    /**
     * @return Mock
     */
    protected function getEventDispatcherMock()
    {
        return $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }

    /**
     * @return Mock
     */
    protected function getTranslatableEntityMock()
    {
        return $this->getMock('Pim\Bundle\TranslationBundle\Entity\AbstractTranslatableEntity');
    }

    /**
     * @param string $field
     * @param string $locale
     * @param string $content
     *
     * @return Mock
     */
    protected function getTranslationMock($field, $locale, $content = null)
    {
        $translation = $this->getMock('Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation');

        $translation->expects($this->any())
                    ->method('getField')
                    ->will($this->returnValue($field));

        $translation->expects($this->any())
                    ->method('getLocale')
                    ->will($this->returnValue($locale));

        $translation->expects($this->any())
                    ->method('getContent')
                    ->will($this->returnValue($content));

        return $translation;
    }
}
