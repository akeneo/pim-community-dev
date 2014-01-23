<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Form\Type;

use Pim\Bundle\CatalogBundle\Form\Type\AttributeType;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeTypeTest extends AbstractFormTypeTest
{
    /**
     * @var \Pim\Bundle\CatalogBundle\Form\Type\AttributeType
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

        // Create a mock for the form and exclude the availableLocales and getAttributeTypeChoices methods
        $this->type = $this->getMock(
            'Pim\Bundle\CatalogBundle\Form\Type\AttributeType',
            ['addFieldAvailableLocales', 'getAttributeTypeChoices', 'addSubscriber'],
            ['Pim\Bundle\CatalogBundle\Entity\Attribute']
        );
        $this->form = $this->factory->create($this->type);
    }

    /**
     * Test build of form with form type
     */
    public function testFormCreate()
    {
        // Assert fields
        $this->assertField('label', 'pim_translatable_field');
        $this->assertField('useableAsGridColumn', 'switch');
        $this->assertField('useableAsGridFilter', 'switch');

        $this->assertField('group', 'entity');

        $this->assertAttributeType();

        // Assert option class
        $this->assertEquals(
            'Pim\Bundle\CatalogBundle\Entity\Attribute',
            $this->form->getConfig()->getDataClass()
        );

        // Assert name
        $this->assertEquals('pim_catalog_attribute', $this->form->getName());
    }

    /**
     * Assert attribute type data
     */
    protected function assertAttributeType()
    {
        $this->assertField('id', 'hidden');
        $this->assertField('code', 'text');
        $this->assertField('attributeType', 'choice');
        $this->assertField('required', 'switch');
    }
}
