<?php
namespace Pim\Bundle\ProductBundle\Tests\Unit\Form\Type;

use Pim\Bundle\ProductBundle\Form\Type\ProductSegmentType;

use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductSegmentTypeTest extends TypeTestCase
{

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        // Create form type
        $this->type = new ProductSegmentType();
        $this->form = $this->factory->create($this->type);
    }

    /**
     * Test build of form with form type
     */
    public function testFormCreate()
    {
        // Assert fields
        $this->assertField('code', 'text');
        $this->assertField('title', 'text');

        // Assert option class
        $this->assertEquals(
            'Pim\Bundle\ProductBundle\Entity\ProductSegment',
            $this->form->getConfig()->getDataClass()
        );

        // Assert name
        $this->assertEquals('pim_product_segment', $this->form->getName());
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
