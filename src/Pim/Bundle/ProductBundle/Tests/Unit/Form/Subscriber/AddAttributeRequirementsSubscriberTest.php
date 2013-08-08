<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Form\Subscriber;

use Pim\Bundle\ProductBundle\Form\Subscriber\AddAttributeRequirementsSubscriber;
use Pim\Bundle\ProductBundle\Entity\Family;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddAttributeRequirementsSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSubscribedEvent()
    {
        $this->assertEquals(
            array('form.pre_set_data' => 'preSetData'),
            AddAttributeRequirementsSubscriber::getSubscribedEvents()
        );
    }

    public function testPreSetData()
    {
        $mobile      = $this->getChannelMock('mobile');
        $ecommerce   = $this->getChannelMock('ecommerce');
        $name        = $this->getAttributeMock('name');
        $description = $this->getAttributeMock('description');

        $channels    = array($mobile, $ecommerce);
        $attributes  = array($name, $description);

        $family      = new Family;
        $event       = $this->getEventMock($family);

        $subscriber  = new AddAttributeRequirementsSubscriber($channels, $attributes);

        $existingRequirement = $this->getAttributeRequirementMock($name, $mobile);
        $family->setAttributeRequirements(array(
            $existingRequirement
        ));

        $subscriber->preSetData($event);

        $requirements = $family->getAttributeRequirements();

        $this->assertArrayHasKey('name_mobile', $requirements);
        $this->assertArrayHasKey('name_ecommerce', $requirements);
        $this->assertArrayHasKey('description_mobile', $requirements);
        $this->assertArrayHasKey('description_ecommerce', $requirements);

        $this->assertAttributeRequirement($requirements, 'name_ecommerce', $family, $name, $ecommerce);
        $this->assertAttributeRequirement($requirements, 'description_mobile', $family, $description, $mobile);
        $this->assertAttributeRequirement($requirements, 'description_ecommerce', $family, $description, $ecommerce);

        $this->assertEquals($requirements['name_mobile'], $existingRequirement);
    }

    private function getAttributeRequirementMock($attribute, $channel)
    {
        $requirement = $this->getMock('Pim\Bundle\ProductBundle\Entity\AttributeRequirement');

        $requirement->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));

        $requirement->expects($this->any())
            ->method('getChannel')
            ->will($this->returnValue($channel));

        return $requirement;
    }

    private function assertAttributeRequirement(array $requirements, $key, $family, $name, $mobile)
    {
        $this->assertEquals($requirements[$key]->getFamily(), $family);
        $this->assertEquals($requirements[$key]->getAttribute(), $name);
        $this->assertEquals($requirements[$key]->getChannel(), $mobile);
    }

    private function getChannelMock($code)
    {
        $channel = $this->getMock('Pim\Bundle\ConfigBundle\Entity\Channel');

        $channel->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        return $channel;
    }

    private function getAttributeMock($code)
    {
        $attribute = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductAttribute');

        $attribute->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        return $attribute;
    }

    private function getEventMock($data)
    {
        $event = $this
            ->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($data));

        return $event;
    }
}
