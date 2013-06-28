<?php
namespace Pim\Bundle\ProductBundle\Tests\Unit\Form\Type;

use Pim\Bundle\ProductBundle\Form\Type\CategoryType;

use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CategoryTypeTest extends AbstractFormTypeTest
{

    /**
     * @var CategoryType
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

        // Create form type
        $this->type = new CategoryType(
            'Pim\Bundle\ProductBundle\Entity\Category',
            'Pim\Bundle\ProductBundle\Entity\CategoryTranslation'
        );
        $this->form = $this->factory->create($this->type);
    }

    /**
     * Test build of form with form type
     */
    public function testFormCreate()
    {
        // Assert fields
        $this->assertField('code', 'text');
        $this->assertField('title', 'pim_translatable_field');

        // Assert option class
        $this->assertEquals(
            'Pim\Bundle\ProductBundle\Entity\Category',
            $this->form->getConfig()->getDataClass()
        );

        // Assert name
        $this->assertEquals('pim_category', $this->form->getName());
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
}
