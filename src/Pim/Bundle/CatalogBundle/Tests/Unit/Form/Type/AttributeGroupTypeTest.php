<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Form\Type;

use Pim\Bundle\CatalogBundle\Form\Type\AttributeGroupType;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    protected function setUp()
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
        $this->assertField('label', 'pim_translatable_field');
        $this->assertField('sort_order', 'hidden');

        // Assert option class
        $this->assertEquals(
            'Pim\Bundle\CatalogBundle\Entity\AttributeGroup',
            $this->form->getConfig()->getDataClass()
        );

        // Assert name
        $this->assertEquals('pim_catalog_attribute_group', $this->form->getName());
    }
}
