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
     * @test
     */
    public function itsPreSetDataShouldAddFormFieldsForEachTranslations()
    {
        $translationFactory = $this->getTranslationFactoryMock();
        $enTranslation      = $this->getTranslationMock('name', 'en_US');
        $frTranslation      = $this->getTranslationMock('name', 'fr_FR');
        $formFactory        = $this->getFormFactoryMock();
        $target             = $this->getTargetedClass('name', 'text', array('fr_FR'), array('en_US', 'fr_FR'), $formFactory, $translationFactory);
        $form               = $this->getFormMock();
        $translatableEntity = $this->getTranslatableEntityMock();
        $event              = $this->getEventMock($form, $translatableEntity, array());

        $translatableEntity->expects($this->once())
                           ->method('setTranslatableLocale')
                           ->with($this->equalTo('default'));

        $translationFactory->expects($this->at(0))
                           ->method('createTranslation')
                           ->with($this->equalTo('en_US'))
                           ->will($this->returnValue($enTranslation));

        $translationFactory->expects($this->at(1))
                           ->method('createTranslation')
                           ->with($this->equalTo('fr_FR'))
                           ->will($this->returnValue($frTranslation));

        $formFactory->expects($this->at(0))
                    ->method('createNamed')
                    ->with(
                        $this->equalTo('name:en_US'),
                        $this->equalTo('text'),
                        $this->equalTo(''),
                        $this->equalTo(array(
                            'label'         => 'en_US',
                            'required'      => false,
                            'property_path' => false,
                        ))
                    )
                    ->will($this->returnValue($enField = $this->getFormMock()));

        $formFactory->expects($this->at(1))
                    ->method('createNamed')
                    ->with(
                        $this->equalTo('name:fr_FR'),
                        $this->equalTo('text'),
                        $this->equalTo(''),
                        $this->equalTo(array(
                            'label'         => 'fr_FR',
                            'required'      => true,
                            'property_path' => false,
                        ))
                    )
                    ->will($this->returnValue($frField = $this->getFormMock()));

        $form->expects($this->any())
             ->method('add')
             ->with($this->equalTo($enField));

        $form->expects($this->any())
             ->method('add')
             ->with($this->equalTo($frField));

        $target->preSetData($event);
    }

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

    protected function getFormFactoryMock()
    {
        return $this
            ->getMockBuilder('Symfony\Component\Form\FormFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('createNamed'))
            ->getMock()
        ;
    }

    protected function getValidatorMock()
    {
        return $this->getMock('Symfony\Component\Validator\ValidatorInterface');
    }

    protected function getTranslationFactoryMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\TranslationBundle\Factory\TranslationFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('createTranslation'))
            ->getMock()
        ;
    }

    protected function getEventMock($form, $parentData = null, array $data = null)
    {
        $event = $this
            ->getMockBuilder('Symfony\Component\Form\Event\DataEvent')
            ->disableOriginalConstructor()
            ->setMethods(array('getData', 'getForm'))
            ->getMock()
        ;

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

    protected function getFormMock()
    {
        $config = $this
            ->getMockBuilder('Symfony\Component\Form\FormConfigBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('getCompound', 'getDataMapper', 'getEventDispatcher'))
            ->getMock()
        ;

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
            ->setMethods(array('getParent', 'getData', 'add'))
            ->getMock()
        ;
    }

    protected function getDataMapperMock()
    {
        return $this->getMock('Symfony\Component\Form\DataMapperInterface');
    }

    protected function getEventDispatcherMock()
    {
        return $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }

    protected function getTranslatableEntityMock()
    {
        return $this->getMock('Pim\Bundle\TranslationBundle\Entity\AbstractTranslatableEntity');
    }

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
