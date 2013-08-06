<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Form\Type;

use Pim\Bundle\ProductBundle\Form\Type\AttributeGroupType;
use Pim\Bundle\ProductBundle\Tests\Entity\AttributeGroupTestEntity;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class AttributeGroupTypeTest extends AbstractFormTypeTest
{
    /**
     * @var AttributeGroupType
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
        $this->type = new AttributeGroupType();
        $this->form = $this->factory->create($this->type);
    }

    /**
     * Test build of form with form type
     */
    public function testFormCreate()
    {
        // Assert fields
        $this->assertField('name', 'pim_translatable_field');
        $this->assertField('sort_order', 'hidden');

        // Assert option class
        $this->assertEquals(
            'Pim\Bundle\ProductBundle\Entity\AttributeGroup',
            $this->form->getConfig()->getDataClass()
        );

        // Assert name
        $this->assertEquals('pim_attribute_group', $this->form->getName());
    }
}
