<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Form\Type;

use Pim\Bundle\FlexibleEntityBundle\Form\Type\MediaType;

use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaTypeTest extends TypeTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->type = new MediaType();
        $this->form = $this->factory->create($this->type);
    }

    /**
     * Test build of form with form type
     */
    public function testFormCreate()
    {
        $this->assertField('file', 'file');
        $this->assertField('removed', 'checkbox');

        $this->assertEquals(
            'Pim\Bundle\FlexibleEntityBundle\Entity\Media',
            $this->form->getConfig()->getDataClass()
        );

        $this->assertEquals('oro_media', $this->form->getName());
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
