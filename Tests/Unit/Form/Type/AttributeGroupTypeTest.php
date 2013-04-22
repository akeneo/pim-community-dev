<?php
namespace Pim\Bundle\ProductBundle\Tests\Unit\Form\Type;

use Pim\Bundle\TranslationBundle\Form\Type\TranslatedFieldType;

use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;

use Symfony\Component\Form\Forms;

use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;

use Pim\Bundle\ProductBundle\Form\Type\AttributeGroupType;

use Pim\Bundle\ProductBundle\Tests\Entity\AttributeGroupTestEntity;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class AttributeGroupTypeTest extends TypeTestCase
{

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        // redefine form factory
        $this->factory = Forms::createFormFactoryBuilder()
            ->addTypeExtension(
                new FormTypeValidatorExtension(
                    $this->getMock('Symfony\Component\Validator\ValidatorInterface')
                )
            )
            ->getFormFactory();

        // Create form type
        $this->type = new AttributeGroupType();
        $this->form = $this->factory->create($this->type);
    }

    /**
     * Test build of form with form type
     */
    public function testFormCreate()
    {
        // Assert fields
        $this->assertField('id', 'hidden');
        $this->assertField('name', 'text');
        $this->assertField('sort_order', 'hidden');

        // Assert option class
        $this->assertEquals(
            'Pim\Bundle\ProductBundle\Entity\AttributeGroup',
            $this->form->getConfig()->getDataClass()
        );

        // Assert name
        $this->assertEquals('pim_attribute_group', $this->form->getName());
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
     * Data provider for success validation of form
     * @return multitype:multitype:multitype:mixed
     *
     * @static
     */
    public static function successProvider()
    {
        return array(
            array(array('id' => 5, 'name' => 'Test-Group', 'sort_order' => 1)),
            array(array('id' => null, 'name' => 'Test-Group', 'sort_order' => 5)),
        );
    }

    /**
     * Test bind data
     * @param array $formData
     *
     * @dataProvider successProvider
     */
    public function testBindValidData($formData)
    {
        // create tested object
        $object = new AttributeGroupTestEntity('\Pim\Bundle\ProductBundle\Entity\AttributeGroup', $formData);

        // bind data and assert data transformer
        $this->form->bind($formData);
        $this->assertTrue($this->form->isSynchronized());
        $this->assertEquals($object->getTestedEntity(), $this->form->getData());

        // assert view renderer
        $view = $this->form->createView();
        $children = $view->getChildren();

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

    /**
     * Data provider for success validation of form
     * @return multitype:multitype:multitype:mixed
     *
     * @static
     */
    public static function failureProvider()
    {
        return array(
            array(array('id' => 5, 'name' => 'Test-Group', 'sort_order' => 'string'))
        );
    }

    /**
     * Test bind data
     * @param array $formData
     *
     * @dataProvider failureProvider
     */
    public function testBindFailedData($formData)
    {
        // create tested object
        $object = new AttributeGroupTestEntity('\Pim\Bundle\ProductBundle\Entity\AttributeGroup', $formData);

        // bind data and assert data transformer
        $this->form->bind($formData);
        $this->assertTrue($this->form->isSynchronized());
        $this->assertNotEquals($object->getTestedEntity(), $this->form->getData());

        // assert view renderer
        $view = $this->form->createView();
        $children = $view->getChildren();

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
