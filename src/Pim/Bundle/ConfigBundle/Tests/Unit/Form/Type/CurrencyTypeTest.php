<?php
namespace Pim\Bundle\ConfigBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Forms;

use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;

use Pim\Bundle\ConfigBundle\Tests\Entity\CurrencyTestEntity;

use Pim\Bundle\ConfigBundle\Form\Type\CurrencyType;

use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CurrencyTypeTest extends TypeTestCase
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
        $this->type = new CurrencyType();
        $this->form = $this->factory->create($this->type);
    }

    /**
     * Test build of form with form type
     */
    public function testFormType()
    {
        // Assert fields
        $this->assertField('id', 'hidden');
        $this->assertField('code', 'choice');
        $this->assertField('label', 'text');
        $this->assertField('activated', 'checkbox');

        // Assert option class
        $this->assertEquals(
            'Pim\Bundle\ConfigBundle\Entity\Currency',
            $this->form->getConfig()->getDataClass()
        );

        // Assert name
        $this->assertEquals('pim_config_currency', $this->form->getName());
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
     */
    public static function successProvider()
    {
        return array(
            array(array('id' => 5, 'code' => 'EUR', 'label' => 'tested label', 'activated' => true))
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
        $object = new CurrencyTestEntity($formData);

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
}
