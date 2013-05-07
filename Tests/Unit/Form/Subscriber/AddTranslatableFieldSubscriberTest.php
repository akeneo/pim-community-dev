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
class AddTranslatableFieldSubscriberTest extends TypeTestCase
{
    /**
     * @var AddTranslatableFieldSubscriber
     */
    protected $subscriber;

    /**
     * @var AttributeGroupType
     */
    protected $type;

    /**
     * @var \Symfony\Component\Form\FormInterface
     */
    protected $form;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        // Create mock container
        $container = $this->getContainerMock();

        // redefine form factory and builder to add translatable field
        $this->builder->add('pim_translatable_field');
        $this->factory = Forms::createFormFactoryBuilder()
            ->addType(new TranslatableFieldType($container))
            ->getFormFactory();

        $this->subscriber = new AddTranslatableFieldSubscriber($this->factory, $container, array());
    }

    /**
     * Test subscriber events
     */
    public function testGetSubscriberEvents()
    {
        $events = $this->subscriber->getSubscribedEvents();

        $this->assertTrue(array_key_exists('form.pre_set_data', $events), 'preSetData');
        $this->assertTrue(array_key_exists('form.post_bind', $events), 'postBind');
        $this->assertTrue(array_key_exists('form.bind', $events), 'bind');
    }

    /**
     * Create mock container for pim_translatable_field
     *
     * @return \Symfony\Component\DependencyInjection\Container
     */
    protected function getContainerMock()
    {
        $localeManager = $this->getLocaleManagerMock();
        $validator = $this->getMock('Symfony\Component\Validator\ValidatorInterface');

        // add locale manager and default locale to container
        $container = new Container();
        $container->set('pim_config.manager.locale', $localeManager);
        $container->set('validator', $validator);
        $container->setParameter('default_locale', 'default');

        return $container;
    }

    /**
     * Create mock for locale manager
     *
     * @return \Pim\Bundle\ConfigBundle\Manager\LocaleManager
     */
    protected function getLocaleManagerMock()
    {
        $objectManager = $this->getMockForAbstractClass('\Doctrine\Common\Persistence\ObjectManager');

        // create mock builder for locale manager and redefine constructor to set object manager
        $mockBuilder = $this->getMockBuilder('Pim\Bundle\ConfigBundle\Manager\LocaleManager')
                            ->setConstructorArgs(array($objectManager));

        // create locale manager mock from mock builder previously create and redefine getActiveCodes method
        $localeManager = $mockBuilder->getMock(
            'Pim\Bundle\ConfigBundle\Manager\LocaleManager',
            array('getActiveCodes')
        );
        $localeManager->expects($this->any())
                      ->method('getActiveCodes')
                      ->will($this->returnValue(array('en_US', 'fr_FR')));

        return $localeManager;
    }
}