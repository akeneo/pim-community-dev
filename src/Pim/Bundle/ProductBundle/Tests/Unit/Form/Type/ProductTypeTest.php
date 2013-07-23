<?php
namespace Pim\Bundle\ProductBundle\Tests\Unit\Form\Type;

use Pim\Bundle\ProductBundle\Manager\ProductManager;
use Pim\Bundle\ProductBundle\Form\Type\ProductType;
use Pim\Bundle\ProductBundle\Entity\Product;
use Pim\Bundle\ProductBundle\Form\View\ProductFormView;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductTypeTest extends AbstractFormTypeTest
{
    /**
     * @var string
     */
    protected $flexibleClass;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->markTestSkipped('Due BAP Upgrade, changes into flexible entity manager');

        parent::setUp();

        $this->flexibleClass = 'Pim\Bundle\ProductBundle\Entity\Product';
        $flexibleManager = new ProductManager(
            $this->flexibleClass,
            array('entities_config' => array($this->flexibleClass => null)),
            $this->getObjectManagerMock(),
            $this->getEventDispatcherInterfaceMock(),
            $this->getAttributeTypeFactoryMock(),
            $this->getMediaManagerMock()
        );

        $type = $this->getMock(
            'Pim\Bundle\ProductBundle\Form\Type\ProductType',
            array('addDynamicAttributesFields'),
            array($flexibleManager, 'text', new ProductFormView) // use text as value form alias
        );

        $type = $this->getMock(
            'Pim\Bundle\ProductBundle\Form\Type\ProductType',
            array('addLocaleField'),
            array($flexibleManager, 'text', new ProductFormView) // use text as value form alias
        );

        $this->form = $this->factory->create($type, new Product());
    }

    /**
     * Get a mock of AttributeTypeFactory
     *
     * @return Oro\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory
     */
    private function getAttributeTypeFactoryMock()
    {
        return $this
            ->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test build of form with form type
     */
    public function testFormCreate()
    {


        $this->assertEquals($this->flexibleClass, $this->form->getConfig()->getDataClass());

        $this->assertEquals('pim_product', $this->form->getName());
    }
}
