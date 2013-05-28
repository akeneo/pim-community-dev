<?php
namespace Pim\Bundle\ProductBundle\Tests\Unit\Form\Type;

use Pim\Bundle\ProductBundle\Manager\ProductManager;

use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;
use Pim\Bundle\ProductBundle\Form\Type\ProductType;
use Pim\Bundle\ProductBundle\Entity\Product;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductTypeTest extends TypeTestCase
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
            array($flexibleManager, 'text') // use text as value form alias
        );

        $type = $this->getMock(
            'Pim\Bundle\ProductBundle\Form\Type\ProductType',
            array('addLocaleField'),
            array($flexibleManager, 'text') // use text as value form alias
        );

        $this->form = $this->factory->create($type, new Product());
    }

    /**
     * Get a mock of ObjectManager
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    private function getObjectManagerMock()
    {
        return $this->getMock('Doctrine\Common\Persistence\ObjectManager');
    }

    /**
     * Get a mock of EventDispatcherInterface
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private function getEventDispatcherInterfaceMock()
    {
        return $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
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
     * Get a mock of MediaManager
     *
     * @return \Pim\Bundle\ProductBundle\Manager\MediaManager
     */
    private function getMediaManagerMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\ProductBundle\Manager\MediaManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test build of form with form type
     */
    public function testFormCreate()
    {
        $this->assertField('sku', 'text');
        $this->assertField('productFamily', 'text');

        $this->assertEquals($this->flexibleClass, $this->form->getConfig()->getDataClass());

        $this->assertEquals('pim_product', $this->form->getName());
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
