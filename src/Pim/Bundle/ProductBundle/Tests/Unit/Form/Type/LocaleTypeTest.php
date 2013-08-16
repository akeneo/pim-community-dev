<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Form\Type;

use Pim\Bundle\ProductBundle\Form\Type\LocaleType;
use Pim\Bundle\ProductBundle\Tests\Unit\Form\Type\AbstractFormTypeTest;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleTypeTest extends AbstractFormTypeTest
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->type = new LocaleType();
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
        $this->assertField('defaultCurrency', 'entity');

        // Assert option class
        $this->assertEquals(
            'Pim\Bundle\ProductBundle\Entity\Locale',
            $this->form->getConfig()->getDataClass()
        );

        // Assert name
        $this->assertEquals('pim_product_locale', $this->form->getName());
    }
}
