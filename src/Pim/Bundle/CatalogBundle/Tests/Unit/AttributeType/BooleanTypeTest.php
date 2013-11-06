<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\AttributeType\BooleanType;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanTypeTest extends AttributeTypeTestCase
{
    protected $name = 'pim_catalog_boolean';
    protected $backendType = AbstractAttributeType::BACKEND_TYPE_BOOLEAN;
    protected $formType = 'switch';

    /**
     * {@inheritdoc}
     */
    protected function createAttributeType()
    {
        return new BooleanType($this->backendType, $this->formType, $this->guesser);
    }

    /**
     * {@inheritdoc}
     */
    public function testBuildValueFormType()
    {
        $factory = $this->getFormFactoryMock();
        $data = true;
        $value = $this->getFlexibleValueMock(
            array(
                'data' => $data,
                'backendType' => $this->backendType
            )
        );

        $factory
            ->expects($this->once())
            ->method('createNamed')
            ->with(
                $this->backendType,
                $this->formType,
                $data,
                array(
                    'constraints' => array('constraints'),
                    'label' => null,
                    'required' => null,
                    'auto_initialize' => false
                )
            );

        $this->target->buildValueFormType($factory, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function testBuildAttributeFormTypes()
    {
        $this->assertCount(
            5,
            $this->target->buildAttributeFormTypes(
                $this->getFormFactoryMock(),
                $this->getAttributeMock(null, null)
            )
        );
    }
}
