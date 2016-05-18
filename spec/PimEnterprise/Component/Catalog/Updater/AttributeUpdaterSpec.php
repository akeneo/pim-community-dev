<?php

namespace spec\PimEnterprise\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Prophecy\Argument;

class AttributeUpdaterSpec extends ObjectBehavior
{
    function let(ObjectUpdaterInterface $attributeUpdater)
    {
        $this->beConstructedWith($attributeUpdater, ['is_read_only']);
    }
    
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\Catalog\Updater\AttributeUpdater');
    }
    
    function it_is_an_updater()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }
    
    function it_updates_field_related_to_the_ee($attributeUpdater, AttributeInterface $attribute)
    {
        $attributeUpdater->update($attribute, ['code' => 'label'], [])->shouldBeCalled();
        $attribute->setProperty('is_read_only', true)->shouldBeCalled();
        
        $this->update($attribute, ['is_read_only' => true, 'code' => 'label'])->shouldReturn($this);
    }
    
    function it_does_not_raise_errors_if_the_field_does_not_exist($attributeUpdater, AttributeInterface $attribute)
    {
        $data = [];

        $attributeUpdater->update($attribute, $data, [])->shouldBeCalled();
        $attribute->setProperty(Argument::cetera())->shouldNotBeCalled();

        $this->update($attribute, $data)->shouldReturn($this);
    }
}
