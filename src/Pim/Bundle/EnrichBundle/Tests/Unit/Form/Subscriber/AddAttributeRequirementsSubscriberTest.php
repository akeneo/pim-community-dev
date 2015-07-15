<?php

namespace Pim\Bundle\EnrichBundle\Tests\Unit\Form\Subscriber;

use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Model\AttributeRequirementInterface;
use Pim\Bundle\EnrichBundle\Form\Subscriber\AddAttributeRequirementsSubscriber;

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
            array(
                'form.pre_set_data'  => 'preSetData',
                'form.post_set_data' => 'postSetData',
            ),
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

        $channelManager = $this->getChannelManagerMock(array($mobile, $ecommerce));

        $name        = $this->getAttributeMock('name');
        $description = $this->getAttributeMock('description');

        $family      = new Family();
        $family->addAttribute($name);
        $family->addAttribute($description);
        $event       = $this->getEventMock($family);

        $subscriber  = new AddAttributeRequirementsSubscriber($channelManager);

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
     * Test related method
     */
    public function testPostSetData()
    {
        $mobile      = $this->getChannelMock('mobile');
        $ecommerce   = $this->getChannelMock('ecommerce');

        $channels    = array($mobile, $ecommerce);

        $requirement1 = $this->getAttributeRequirementMock($this->getAttributeMock('', 'bar'));
        $requirement2 = $this->getAttributeRequirementMock($this->getAttributeMock('', 'pim_catalog_identifier'));
        $requirement2->expects($this->once())
            ->method('setRequired')
            ->with(true);
        $family = $this->getFamilyMock(
            array(
                'foo' => $requirement1,
                'baz' => $requirement2
            )
        );

        $form   = $this->getFormMock();
        $event  = $this->getEventMock($family, $form);

        $requirementsForm = $this->getFormMock();
        $form->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValueMap(
                    array(
                        array('attributeRequirements', $requirementsForm)
                    )
                )
            );
        $requirementsForm->expects($this->once())
            ->method('remove')
            ->with('baz');

        $subscriber  = new AddAttributeRequirementsSubscriber($this->getChannelManagerMock($channels));
        $subscriber->postSetData($event);
    }

    /**
     * @param mixed $attribute
     * @param mixed $channel
     *
     * @return AttributeRequirementInterface
     */
    private function getAttributeRequirementMock($attribute, $channel = null)
    {
        $requirement = $this->getMock('Pim\Bundle\CatalogBundle\Entity\AttributeRequirement');

        $requirement->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));

        $requirement->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue($attribute->getCode()));

        $requirement->expects($this->any())
            ->method('getChannel')
            ->will($this->returnValue($channel));

        if ($channel) {
            $requirement->expects($this->any())
                ->method('getChannelCode')
                ->will($this->returnValue($channel->getCode()));
        }

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
     * @param string $type
     *
     * @return \Pim\Bundle\CatalogBundle\Model\AttributeInterface
     */
    private function getAttributeMock($code, $type = null)
    {
        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Attribute');

        $attribute->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        $attribute->expects($this->any())
            ->method('getAttributeType')
            ->will($this->returnValue($type));

        return $attribute;
    }

    /**
     * @param mixed $data
     * @param Form  $form
     *
     * @return \Symfony\Component\Form\FormEvent
     */
    private function getEventMock($data, $form = null)
    {
        $event = $this
            ->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($data));

        $event->expects($this->any())
            ->method('getForm')
            ->will($this->returnValue($form));

        return $event;
    }

    /**
     * @param array $requirements
     *
     * @return Family
     */
    protected function getFamilyMock(array $requirements)
    {
        $family = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Family');

        $family->expects($this->any())
            ->method('getAttributeRequirements')
            ->will($this->returnValue($requirements));

        return $family;
    }

    /**
     * @return \Symfony\Component\Form\Form
     */
    protected function getFormMock()
    {
        return $this
            ->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Get channel manager mock
     *
     * @param array $channels
     *
     * @return ChannelManager
     */
    protected function getChannelManagerMock(array $channels = array())
    {
        $channelManager = $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\ChannelManager')
            ->disableOriginalConstructor()
            ->getMock();

        $channelManager
            ->expects($this->any())
            ->method('getChannels')
            ->will($this->returnValue($channels));

        return $channelManager;
    }
}
