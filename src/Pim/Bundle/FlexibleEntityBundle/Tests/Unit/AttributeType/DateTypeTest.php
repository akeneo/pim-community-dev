<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\DateType;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTypeTest extends AttributeTypeTest
{
    protected $name  = 'pim_flexibleentity_date';

    protected function setUp()
    {
        parent::setUp();

        $this->target = new DateType('integer', 'date', $this->guesser);
    }

    public function testBuildValueFormType()
    {
        $factory = $this->getFormFactoryMock();
        $value = $this->getFlexibleValueMock(
            array(
                'data'        => 'bar',
                'backendType' => 'foo',
            )
        );

        $factory->expects($this->once())
            ->method('createNamed')
            ->with(
                'foo',
                'date',
                'bar',
                array_merge(
                    $this->defaultCreateNamedOptions,
                    array(
                        'widget' => 'single_text',
                        'input'  => 'datetime',
                    )
                )
            );

        $this->target->buildValueFormType($factory, $value);
    }

    public function testGetBackendType()
    {
        $this->assertEquals('integer', $this->target->getBackendType());
    }

    public function testGetFormType()
    {
        $this->assertEquals('date', $this->target->getFormType());
    }

    public function testBuildAttributeFormTypes()
    {
        $this->assertCount(
            4,
            $this->target->buildAttributeFormTypes(
                $this->getFormFactoryMock(),
                $this->getAttributeMock(null, null)
            )
        );
    }
}
