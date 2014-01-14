<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\AttributeType\DateType;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTypeTest extends AttributeTypeTestCase
{
    protected $name = 'pim_catalog_date';
    protected $backendType = AbstractAttributeType::BACKEND_TYPE_DATE;
    protected $formType = 'date';

    /**
     * {@inheritdoc}
     */
    protected function createAttributeType()
    {
        return new DateType($this->backendType, $this->formType, $this->guesser);
    }

    /**
     * Data provider for build value form type method
     *
     * @return array
     */
    public static function buildValueFormTypeDataProvider()
    {
        return array(
            array(
                array(),
                array('widget' => 'single_text', 'input' => 'datetime')
            )
        );
    }

    /**
     * {@inheritdoc}
     *
     * @dataProvider buildValueFormTypeDataProvider
     */
    public function testBuildValueFormType($attributeOptions, $expectedResult)
    {
        $factory = $this->getFormFactoryMock();
        $data = '12/06/2013';
        $value = $this->getFlexibleValueMock(
            array(
                'data'        => $data,
                'backendType' => $this->backendType,
                'attribute_options' => $attributeOptions
            )
        );

        $factory
            ->expects($this->once())
            ->method('createNamed')
            ->with(
                $this->backendType,
                $this->formType,
                $data,
                array_merge(
                    array(
                        'constraints'     => array('constraints'),
                        'label'           => null,
                        'required'        => null,
                        'auto_initialize' => false
                    ),
                    $expectedResult
                )
            );

        $this->target->buildValueFormType($factory, $value);
    }

    /**
     * Test related method
     */
    public function testBuildAttributeFormTypes()
    {
        $attFormType = $this->target->buildAttributeFormTypes(
            $this->getFormFactoryMock(),
            $this->getAttributeMock(null, null)
        );

        $this->assertCount(
            9,
            $attFormType
        );
    }
}
