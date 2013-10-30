<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Form\Subscriber;

use Pim\Bundle\CatalogBundle\Form\Subscriber\AddAttributeRequirementsSubscriber;
use Pim\Bundle\CatalogBundle\Entity\Family;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddAttributeRequirementsSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test related method
     */
    public function testGetSubscribedEvent()
    {
        $this->assertEquals(
            array('form.pre_set_data' => 'preSetData'),
            AddAttributeRequirementsSubscriber::getSubscribedEvents()
        );
    }

    /**
     * Test related method
     */
    public function testPreSetData()
    {
        $mobile      = $this->getChannelMock('mobile');
        $ecommerce   = $this->getChannelMock('ecommerce');
        $name        = $this->getAttributeMock('name');
        $description = $this->getAttributeMock('description');

        $channels    = array($mobile, $ecommerce);
        $attributes  = array($name, $description);

        $family      = new Family();
        $event       = $this->getEventMock($family);

        $subscriber  = new AddAttributeRequirementsSubscriber($channels, $attributes);

        $existingRequirement = $this->getAttributeRequirementMock($name, $mobile);
        $family->setAttributeRequirements(array($existingRequirement));

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

    /**
     * @param mixed $attribute
     * @param mixed $channel
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\AttributeRequirement
     */
    private function getAttributeRequirementMock($attribute, $channel)
    {
        $requirement = $this->getMock('Pim\Bundle\CatalogBundle\Entity\AttributeRequirement');

        $requirement->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));

        $requirement->expects($this->any())
            ->method('getChannel')
            ->will($this->returnValue($channel));

        return $requirement;
    }

    /**
     * @param array  $requirements
     * @param string $key
     * @param mixed  $family
     * @param mixed  $name
     * @param mixed  $mobile
     */
    private function assertAttributeRequirement(array $requirements, $key, $family, $name, $mobile)
    {
        $this->assertEquals($requirements[$key]->getFamily(), $family);
        $this->assertEquals($requirements[$key]->getAttribute(), $name);
        $this->assertEquals($requirements[$key]->getChannel(), $mobile);
    }

    /**
     * @param string $code
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Channel
     */
    private function getChannelMock($code)
    {
        $channel = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Channel');

        $channel->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        return $channel;
    }

    /**
     * @param string $code
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\ProductAttribute
     */
    private function getAttributeMock($code)
    {
        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductAttribute');

        $attribute->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        return $attribute;
    }

    /**
     * @param mixed $data
     *
     * @return \Symfony\Component\Form\FormEvent
     */
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
