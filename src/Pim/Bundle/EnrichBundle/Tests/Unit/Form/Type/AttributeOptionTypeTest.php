<?php

namespace Pim\Bundle\EnrichBundle\Tests\Unit\Form\Type;

use Pim\Bundle\EnrichBundle\Form\Type\AttributeOptionType;
use Pim\Bundle\EnrichBundle\Form\Type\AttributeOptionValueType;
use Symfony\Component\Form\PreloadedExtension;

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

        return array(
            new PreloadedExtension(
                array(
                    $optionValueType->getName() => $optionValueType,
                ),
                array()
            )
        );
    }

    /**
     * Test build of form with form type
     */
    public function testFormCreate()
    {
        $this->assertField('id', 'hidden');

        $this->assertEquals(
            'Pim\Bundle\CatalogBundle\Entity\AttributeOption',
            $this->form->getConfig()->getDataClass()
        );

        $this->assertEquals('pim_enrich_attribute_option', $this->form->getName());
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
