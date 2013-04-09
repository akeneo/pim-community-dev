<?php
namespace Pim\Bundle\TranslationBundle\Tests\Form\Type;

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
class TranslationTypeTest extends TypeTestCase
{

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        // redefine form factory
        $this->factory = Forms::createFormFactoryBuilder()->getFormFactory();

        // create form type
        $this->type = $this->getMockForAbstractClass('Pim\Bundle\TranslationBundle\Form\Type\TranslationType');
        $this->form = $this->factory->create($this->type);
    }

    /**
     * Test related method
     */
    public function testFormCreate()
    {
        // Assert fields
        $this->assertField('content', 'text');
        $this->assertField('locale', 'text');
    }

    /**
     * Assert field name and type
     *
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
     *
     * @return multitype:multitype:multitype:string
     *
     * @static
     */
    public static function successProvider()
    {
        return array(
            array(array('content' => 'Content', 'locale' => 'en_US'))
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
        // bind data and assert data is synchronized
        $this->form->bind($formData);
        $this->assertTrue($this->form->isSynchronized());

        // assert view renderer
        $view = $this->form->createView();
        $children = $view->getChildren();

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
