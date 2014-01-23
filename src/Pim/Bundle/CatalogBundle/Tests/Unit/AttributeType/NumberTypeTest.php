<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\AttributeType\NumberType;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberTypeTest extends AttributeTypeTestCase
{
    protected $name = 'pim_catalog_number';
    protected $backendType = AbstractAttributeType::BACKEND_TYPE_DECIMAL;
    protected $formType = 'number';

    /**
     * {@inheritdoc}
     */
    protected function createAttributeType()
    {
        return new NumberType($this->backendType, $this->formType, $this->guesser);
    }

    /**
     * Data provider for build value form type method
     *
     * @return array
     */
    public static function buildValueFormTypeDataProvider()
    {
        return [
            'decimals_allowed'     => [
                ['is_decimals_allowed' => true],
                ['precision' => 4]
            ],
            'decimals_now_allowed' => [
                ['is_decimals_allowed' => false],
                ['precision' => 0]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @dataProvider buildValueFormTypeDataProvider
     */
    public function testBuildValueFormType($attributeOptions, $expectedResult)
    {
        $factory = $this->getFormFactoryMock();
        $data = 5;
        $value = $this->getFlexibleValueMock(
            [
                'data'        => $data,
                'backendType' => $this->backendType,
                'attribute_options' => $attributeOptions
            ]
        );

        $factory
            ->expects($this->once())
            ->method('createNamed')
            ->with(
                $this->backendType,
                $this->formType,
                $data,
                array_merge(
                    [
                        'constraints'     => ['constraints'],
                        'label'           => null,
                        'required'        => null,
                        'auto_initialize' => false
                    ],
                    $expectedResult
                )
            );

        $this->target->buildValueFormType($factory, $value);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAttributeMock($backendType, $defaultValue, array $attributeOptions = [])
    {
        $attribute = parent::getAttributeMock($backendType, $defaultValue, $attributeOptions);

        if (!isset($attributeOptions['is_decimals_allowed'])) {
            $attributeOptions['is_decimals_allowed'] = true;
        }
        $attribute
            ->expects($this->any())
            ->method('isDecimalsAllowed')
            ->will($this->returnValue($attributeOptions['is_decimals_allowed']));

        return $attribute;
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
            10,
            $attFormType
        );
    }
}
