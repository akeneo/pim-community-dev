<?php
namespace Pim\Bundle\ProductBundle\Tests\Form\Type;

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
     * @var string
     */
    protected $valueClass;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->flexibleClass = 'Pim\Bundle\ProductBundle\Entity\Product';
        $this->valueClass    = 'Pim\Bundle\ProductBundle\Entity\ProductValue';

        $type = $this->getMock(
            'Pim\Bundle\ProductBundle\Form\Type\ProductType',
            array('addDynamicAttributesFields'),
            array($this->flexibleClass, $this->valueClass)
        );
        $this->form = $this->factory->create($type, new Product());
    }

    /**
     * Test build of form with form type
     */
    public function testFormCreate()
    {
        $this->assertField('sku', 'text');
        $this->assertField('productFamily', 'text');

        $this->assertEquals($this->flexibleClass, $this->form->getConfig()->getDataClass());

        $this->assertEquals('oro_flexibleentity_entity', $this->form->getName());
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
