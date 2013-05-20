<?php
namespace Pim\Bundle\ProductBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;
use Symfony\Component\Form\Forms;
use Pim\Bundle\ProductBundle\Form\Type\ProductFamilyType;
use Pim\Bundle\TranslationBundle\Form\Type\TranslatableFieldType;

/**
 * Test related class
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductFamilyTypeTest extends TypeTestCase
{

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
            ->addType(new TranslatableFieldType(
                $this->getMock('Symfony\Component\Validator\ValidatorInterface'),
                $this->getLocaleManagerMock(),
                'en_US'
            ))
            ->getFormFactory();

        // Create form type
        $this->type = new ProductFamilyType();
        $this->form = $this->factory->create($this->type);
    }

    /**
     * Test build of form with form type
     */
    public function testFormCreate()
    {
        // Assert fields
        $this->assertField('code', 'text');

        // Assert option class
        $this->assertEquals(
            'Pim\Bundle\ProductBundle\Entity\ProductFamily',
            $this->form->getConfig()->getDataClass()
        );

        // Assert name
        $this->assertEquals('pim_product_family', $this->form->getName());
    }

    /**
     * Assert field name and type
     * @param string $name Field name
     * @param string $type Field type alias
     */
    protected function assertField($name, $type)
    {
        $formType = $this->form->get($name);
        $this->assertInstanceOf('\Symfony\Component\Form\Form', $formType);
        $this->assertEquals($type, $formType->getConfig()->getType()->getInnerType()->getName());
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
        $localeManager->expects($this->once())
                      ->method('getActiveCodes')
                      ->will($this->returnValue(array('en_US', 'fr_FR')));

        return $localeManager;
    }
}
