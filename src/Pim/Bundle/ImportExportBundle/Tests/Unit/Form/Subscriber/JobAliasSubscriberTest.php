<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Form\Subscriber;

use Oro\Bundle\BatchBundle\Entity\JobInstance;

use Pim\Bundle\ImportExportBundle\Form\Subscriber\JobAliasSubscriber;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobAliasSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test related method
     */
    public function testGetSubscribedEvent()
    {
        $this->assertEquals(
            array('form.bind' => 'submit'),
            JobAliasSubscriber::getSubscribedEvents()
        );
    }

    /**
     * Test related method
     */
    public function testSubmit()
    {
        $jobInstance = new JobInstance(null, null, null);
        $form        = $this->getFormMock();
        $event       = $this->getEventMock($jobInstance, $form);

        // Form mock
        $formConnector = $this->getFormMock();
        $formAlias = $this->getFormMock();
        $formMap = array(
            array('connector', $formConnector),
            array('alias', $formAlias)
        );
        $form
            ->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValueMap($formMap)
            );
        $formConnector
            ->expects($this->once())
            ->method('getData')
            ->will($this->returnValue('myConnector'));
        $formAlias
            ->expects($this->once())
            ->method('getData')
            ->will($this->returnValue('myAlias'));

        $subscriber = new JobAliasSubscriber();
        $subscriber->submit($event);

        $this->assertEquals('myConnector', $jobInstance->getConnector());
        $this->assertEquals('myAlias', $jobInstance->getAlias());
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
}
