<?php
namespace Pim\Bundle\TranslationBundle\Tests\Unit\Form\Type;

use Pim\Bundle\TranslationBundle\Form\Type\TranslatableFieldType;

use Symfony\Component\DependencyInjection\Container;

use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;

use Symfony\Component\Form\Forms;

use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class TranslatableFieldTypeTest extends TypeTestCase
{

    /**
     * @var TranslatableFieldType
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
            ->addTypeExtension(
                new FormTypeValidatorExtension(
                    $this->getMock('Symfony\Component\Validator\ValidatorInterface')
                )
            )
            ->addType(new TranslatableFieldType($container))
            ->getFormFactory();

        // Create form type
        $this->type = new TranslatableFieldType($container);
        $options = array(
            'entity_class'      => 'Pim\\Bundle\\TranslationBundle\\Tests\\Entity\\Item',
            'translation_class' => 'Pim\\Bundle\\TranslationBundle\\Tests\\Entity\\ItemTranslation'
        );


        $this->form = $this->factory->create($this->type, null, $options);
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

    public function testFormCreate()
    {
        // Assert default options
        $options = $this->type->getDefaultOptions();

        // Assert name
        $this->assertEquals('pim_translatable_field', $this->form->getName());
    }



    // TODO : Tests with unexistent classes in options
}
