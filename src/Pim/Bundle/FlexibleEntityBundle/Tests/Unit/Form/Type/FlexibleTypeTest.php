<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Forms;
use Pim\Bundle\FlexibleEntityBundle\Tests\Unit\AbstractFlexibleManagerTest;
use Pim\Bundle\FlexibleEntityBundle\Form\Type\FlexibleType;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleTypeTest extends AbstractFlexibleManagerTest
{
    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $factory;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions(array())
            ->getFormFactory();
        $this->markTestSkipped('BAP-872: Fix issue with "cascade_validation" does not exist in unit tests');
        $this->type = new FlexibleType($this->manager, 'text');
        $this->form = $this->factory->create($this->type);
    }

    /**
     * Test build of form with form type
     */
    public function testFormCreate()
    {
        $this->assertField('id', 'hidden');

        $this->assertEquals(
            'Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Demo\Flexible',
            $this->form->getConfig()->getDataClass()
        );

        $this->assertEquals('pim_flexibleentity_entity', $this->form->getName());
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
