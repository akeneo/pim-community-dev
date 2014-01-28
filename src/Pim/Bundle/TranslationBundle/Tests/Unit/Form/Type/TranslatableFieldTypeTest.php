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
    protected function setUp()
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
                    $this->getLocaleHelperMock()
                )
            )
            ->getFormFactory();

        // Create form type
        $this->type = new TranslatableFieldType(
            $this->getMock('Symfony\Component\Validator\ValidatorInterface'),
            $this->getLocaleManagerMock(),
            $this->getLocaleHelperMock()
        );
        $this->options = $this->buildOptions(self::OPT_ENTITY_CLASS, self::OPT_NAME, self::OPT_TRANSLATION_CLASS);

        $this->form = $this->factory->create($this->type, null, $this->options);
    }

    /**
     * Create mock for locale manager
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\LocaleManager
     */
    protected function getLocaleManagerMock()
    {
        $manager = $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\LocaleManager')
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects($this->any())
            ->method('getActiveCodes')
            ->will($this->returnValue(array('en_US', 'fr_FR')));

        return $manager;
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
            $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
        );

        return $securityContext;
    }

    /**
     * Test method related
     */
    public function testFormCreate()
    {
        // Assert default options
        $expectedOptions = array(
            'entity_class'      => false,
            'field'             => false,
            'locales'           => array('en_US', 'fr_FR'),
            'required_locale'   => array(),
            'translation_class' => false,
            'widget'            => 'text'
        );

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
     * @return string[]
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
     * @return array
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
        $this->factory->create($this->type, null, $options);
    }

    /**
     * Get LocaleHelperMock
     *
     * @return \Pim\Bundle\CatalogBundle\Helper\LocaleHelper
     */
    protected function getLocaleHelperMock()
    {
        $helper = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Helper\LocaleHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $helper->expects($this->any())
            ->method('getLocaleLabel')
            ->will($this->returnArgument(0));

        return $helper;
    }

    /**
     * Get ACL SecurityFacade mock
     *
     * @return \Oro\Bundle\SecurityBundle\SecurityFacade
     */
    protected function getSecurityFacadeMock()
    {
        return $this
            ->getMockBuilder('Oro\Bundle\SecurityBundle\SecurityFacade')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
