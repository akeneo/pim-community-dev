<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Form\Type;

use Pim\Bundle\ProductBundle\Form\Type\FamilyType;

/**
 * Test related class
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class FamilyTypeTest extends AbstractFormTypeTest
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        // Create form type
        $this->type = new FamilyType();
        $this->form = $this->factory->create($this->type);
    }

    /**
     * Test build of form with form type
     */
    public function testFormCreate()
    {
        // Assert fields
        $this->assertField('code', 'text');

        // Assert option class
        $this->assertEquals(
            'Pim\Bundle\ProductBundle\Entity\Family',
            $this->form->getConfig()->getDataClass()
        );

        // Assert name
        $this->assertEquals('pim_family', $this->form->getName());
    }
}
