<?php
namespace Oro\Bundle\AddressBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\AddressType;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\AddressBundle\Form\EventListener\BuildAddressFormListener;

class AddressTypeTest extends AbstractAddressTypeTest
{
    /**
     * @var AbstractAddressTypeTest
     */
    protected $type;

    protected function createTestAddress(
        FlexibleManager $flexibleManager,
        $valueFormAlias,
        BuildAddressFormListener $eventListener
    ) {
        return new AddressType($flexibleManager, $valueFormAlias, $eventListener);
    }

    public function testGetName()
    {
        $this->assertEquals('oro_address', $this->type->getName());
    }
}
