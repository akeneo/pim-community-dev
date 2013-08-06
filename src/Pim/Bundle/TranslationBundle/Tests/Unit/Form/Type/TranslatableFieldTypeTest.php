<?php

namespace Pim\Bundle\TranslationBundle\Tests\Unit\Form\Type;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;
use Pim\Bundle\TranslationBundle\Form\Type\TranslatableFieldType;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
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
     * @var array
     */
    protected $options;

    /**
     * @staticvar string
     */
    const DEFAULT_LOCALE = 'default';

    /**
     * @staticvar string
     */
    const OPT_NAME = 'name';

    /**
     * @staticvar string
     */
    const OPT_ENTITY_CLASS = 'Pim\\Bundle\\TranslationBundle\\Tests\\Entity\\Item';

    /**
     * @staticvar string
     */
    const OPT_TRANSLATION_CLASS = 'Pim\\Bundle\\TranslationBundle\\Tests\\Entity\\ItemTranslation';

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        // redefine form factory and builder to add translatable field
        $this->builder->add('pim_translatable_field');
        $this->factory = Forms::createFormFactoryBuilder()
            ->addTypeExtension(
                new FormTypeValidatorExtension(
                    $this->getMock('Symfony\Component\Validator\ValidatorInterface')
                )
            )
            ->addType(
                new TranslatableFieldType(
                    $this->getMock('Symfony\Component\Validator\ValidatorInterface'),
                    $this->getLocaleManagerMock(),
                    self::DEFAULT_LOCALE
                )
            )
            ->getFormFactory();

        // Create form type
        $this->type = new TranslatableFieldType(
            $this->getMock('Symfony\Component\Validator\ValidatorInterface'),
            $this->getLocaleManagerMock(),
            self::DEFAULT_LOCALE
        );
        $this->options = $this->buildOptions(self::OPT_ENTITY_CLASS, self::OPT_NAME, self::OPT_TRANSLATION_CLASS);

        $this->form = $this->factory->create($this->type, null, $this->options);
    }

    /**
     * Create mock for locale manager
     *
     * @return \Pim\Bundle\ConfigBundle\Manager\LocaleManager
     */
    protected function getLocaleManagerMock()
    {
        $objectManager = $this->getMockForAbstractClass('\Doctrine\Common\Persistence\ObjectManager');
        $securityContext = $this->getSecurityContextMock();

        // create mock builder for locale manager and redefine constructor to set object manager
        $mockBuilder = $this->getMockBuilder('Pim\Bundle\ConfigBundle\Manager\LocaleManager')
                            ->setConstructorArgs(array($objectManager, $securityContext));

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

    /**
     * Create a security context mock
     *
     * @return \Symfony\Component\Security\Core\SecurityContext
     */
    protected function getSecurityContextMock()
    {
        $authManager = $this->getMock('Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface');
        $decisionManager = $this->getMock(
            'Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface'
        );

        $securityContext = new SecurityContext($authManager, $decisionManager);
        $securityContext->setToken(
            $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
        );

        return $securityContext;
    }

    /**
     * Test method related
     */
    public function testFormCreate()
    {
        // Assert default options
        $options = $this->type->getDefaultOptions();
        $expectedOptions = array(
            'default_locale'    => self::DEFAULT_LOCALE,
            'entity_class'      => false,
            'field'             => false,
            'locales'           => array(self::DEFAULT_LOCALE, 'en_US', 'fr_FR'),
            'required_locale'   => array(self::DEFAULT_LOCALE),
            'translation_class' => false,
            'widget'            => 'text'
        );
        foreach ($expectedOptions as $expectedKey => $expectedValue) {
            $this->assertArrayHasKey($expectedKey, $options);
            $this->assertEquals($expectedValue, $options[$expectedKey]);
        }

        // Assert options
        $options = $this->form->getConfig()->getOptions();
        $expectedOptions['entity_class']      = self::OPT_ENTITY_CLASS;
        $expectedOptions['field']             = self::OPT_NAME;
        $expectedOptions['translation_class'] = self::OPT_TRANSLATION_CLASS;
        foreach ($expectedOptions as $expectedKey => $expectedValue) {
            $this->assertArrayHasKey($expectedKey, $options);
            $this->assertEquals($expectedValue, $options[$expectedKey]);
        }

        // Assert name
        $this->assertEquals('pim_translatable_field', $this->form->getName());
    }

    /**
     * Create options
     *
     * @param string $entityClass      Entity class name
     * @param string $fieldName        Entity field name
     * @param string $translationClass Translation class name
     *
     * @return multitype:string
     */
    protected function buildOptions($entityClass, $fieldName, $translationClass)
    {
        return array(
            'entity_class'      => $entityClass,
            'field'             => $fieldName,
            'translation_class' => $translationClass
        );
    }

    /**
     * Data provider for options
     *
     * @static
     *
     * @return multitype:multitype:string
     */
    public static function dataOptionsProvider()
    {
        return array(
            array(self::OPT_ENTITY_CLASS, self::OPT_NAME, null),
            array(self::OPT_ENTITY_CLASS, null, self::OPT_TRANSLATION_CLASS),
            array(null, self::OPT_NAME, self::OPT_TRANSLATION_CLASS)
        );
    }

    /**
     * Assert buildForm exceptions
     *
     * @param string $entityClass      Entity class name
     * @param string $fieldName        Entity field name
     * @param string $translationClass Translation class name
     *
     * @dataProvider dataOptionsProvider
     * @expectedException \Symfony\Component\Form\Exception\InvalidConfigurationException
     */
    public function testAssertException($entityClass, $fieldName, $translationClass)
    {
        $options = $this->buildOptions($entityClass, $fieldName, $translationClass);
        $form = $this->factory->create($this->type, null, $options);
    }
}
