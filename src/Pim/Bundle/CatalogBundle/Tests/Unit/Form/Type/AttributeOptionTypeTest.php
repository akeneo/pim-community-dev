<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\PreloadedExtension;
use Pim\Bundle\CatalogBundle\Form\Type\AttributeOptionType;
use Pim\Bundle\CatalogBundle\Form\Type\AttributeOptionValueType;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionTypeTest extends AbstractFormTypeTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->type = new AttributeOptionType();
        $this->form = $this->factory->create($this->type);
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        $optionValueType = new AttributeOptionValueType();

        return [
            new PreloadedExtension(
                [
                    $optionValueType->getName() => $optionValueType,
                ],
                []
            )
        ];
    }

    /**
     * Test build of form with form type
     */
    public function testFormCreate()
    {
        $this->assertField('id', 'hidden');
        $this->assertField('sort_order', 'integer');
        $this->assertField('translatable', 'text');

        $this->assertEquals(
            'Pim\Bundle\CatalogBundle\Entity\AttributeOption',
            $this->form->getConfig()->getDataClass()
        );

        $this->assertEquals('pim_catalog_attribute_option', $this->form->getName());
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
