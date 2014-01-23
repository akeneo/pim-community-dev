<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\PhoneCollectionType;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PhoneCollectionTypeTest extends AttributeTypeTest
{
    protected $name = 'pim_flexibleentity_phone_collection';

    protected function setUp()
    {
        parent::setUp();

        $this->target = new PhoneCollectionType('collections', 'text', $this->guesser);
    }

    public function testBuildValueFormType()
    {
        $factory = $this->getFormFactoryMock();
        $phone = $this->getFlexibleValueMock([]);
        $value = $this->getFlexibleValueMock(
            [
                'data'        => $phone,
                'backendType' => 'foo',
            ]
        );

        $factory->expects($this->once())
            ->method('createNamed')
            ->with(
                'foo',
                'text',
                $phone,
                $this->defaultCreateNamedOptions
            );

        $this->target->buildValueFormType($factory, $value);
    }

    public function testGetBackendType()
    {
        $this->assertEquals('collections', $this->target->getBackendType());
    }

    public function testGetFormType()
    {
        $this->assertEquals('text', $this->target->getFormType());
    }

    public function testBuildAttributeFormTypes()
    {
        $this->assertEquals(
            [],
            $this->target->buildAttributeFormTypes(
                $this->getFormFactoryMock(),
                $this->getAttributeMock(null, null)
            )
        );
    }
}
