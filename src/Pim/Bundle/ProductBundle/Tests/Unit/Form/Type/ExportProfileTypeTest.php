<?php
namespace Pim\Bundle\ProductBundle\Tests\Unit\Form\Type;

use Pim\Bundle\ProductBundle\Tests\Entity\ObjectTestEntity;

use Pim\Bundle\ProductBundle\Form\Type\ExportProfileType;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ExportProfileTypeTest extends AbstractFormTypeTest
{

    /**
     * @var ExportProfileType
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
        $this->type = new ExportProfileType();
        $this->form = $this->factory->create($this->type);
    }

    /**
     * Test build of form with form type
     */
    public function testFormCreate()
    {
        // Assert fields
        $this->assertField('code', 'text');
        $this->assertField('name', 'pim_translatable_field');

        // Assert option class
        $this->assertEquals(
            'Pim\Bundle\ProductBundle\Entity\ExportProfile',
            $this->form->getConfig()->getDataClass()
        );

        // Assert name
        $this->assertEquals('pim_export_profile', $this->form->getName());
    }
}
