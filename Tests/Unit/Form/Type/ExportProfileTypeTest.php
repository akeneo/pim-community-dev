<?php
namespace Pim\Bundle\ProductBundle\Tests\Unit\Form\Type;

use Pim\Bundle\ProductBundle\Tests\Entity\ObjectTestEntity;

use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;

use Symfony\Component\Form\Forms;

use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;

use Pim\Bundle\ProductBundle\Form\Type\ExportProfileType;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ExportProfileTypeTest extends TypeTestCase
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
        $this->type = new ExportProfileType();
        $this->form = $this->factory->create($this->type);
    }

    /**
     * Test build of form with form type
     */
    public function testFormCreate()
    {
        // Assert fields
        $this->assertField('id', 'hidden');
        $this->assertField('code', 'text');
        $this->assertField('name', 'text');

        // Assert option class
        $this->assertEquals(
            'Pim\Bundle\ProductBundle\Entity\ExportProfile',
            $this->form->getConfig()->getDataClass()
        );

        // Assert name
        $this->assertEquals('pim_export_profile', $this->form->getName());
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
            array(array('id' => 5, 'name' => 'Test-Profile', 'sort_order' => 1)),
            array(array('id' => null, 'name' => 'Test-Profile', 'sort_order' => 5)),
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
        $object = new ExportProfileTestEntity('\Pim\Bundle\ProductBundle\Entity\ExportProfile', $formData);

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
        $object = new ObjectTestEntity('\Pim\Bundle\ProductBundle\Entity\ExportProfile', $formData);

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
