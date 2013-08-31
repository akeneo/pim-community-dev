<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Form\Type;

use Pim\Bundle\CatalogBundle\Tests\Unit\Form\Type\AbstractFormTypeTest;
use Pim\Bundle\CatalogBundle\Tests\Entity\ObjectTestEntity;
use Pim\Bundle\CatalogBundle\Form\Type\ChannelType;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelTypeTest extends AbstractFormTypeTest
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->markTestIncomplete('Find a way to test the "pim_product_available_locales" form type or drop this test');

        // Create form type
        $this->type = new ChannelType($config);
        $this->form = $this->factory->create($this->type);
    }

    /**
     * Test build of form with form type
     */
    public function testFormType()
    {
        // Assert fields
        $this->assertField('id', 'hidden');
        $this->assertField('code', 'text');
        $this->assertField('name', 'text');

        // Assert option class
        $this->assertEquals(
            'Pim\Bundle\CatalogBundle\Entity\Channel',
            $this->form->getConfig()->getDataClass()
        );

        // Assert name
        $this->assertEquals('pim_product_channel', $this->form->getName());
    }

    /**
     * Data provider for success validation of form
     * @return multitype:multitype:multitype:mixed
     *
     * @static
     */
    public static function successProvider()
    {
        return array(
            array(array('id' => 5, 'code' => 'ecommerce', 'name' => 'E-Commerce')),
            array(array('id' => null, 'code' => 'ecommerce', 'name' => 'E-Commerce'))
        );
    }

    /**
     * Test bind data
     * @param array $formData
     *
     * @dataProvider successProvider
     */
    public function testBindValidData($formData)
    {
        // create tested object
        $object = new ObjectTestEntity('\Pim\Bundle\CatalogBundle\Entity\Channel', $formData);

        // bind data and assert data transformer
        $this->form->bind($formData);
        $this->assertTrue($this->form->isSynchronized());
        $this->assertEquals($object->getTestedEntity(), $this->form->getData());

        // assert view renderer
        $view = $this->form->createView();
        $children = $view->getChildren();

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
