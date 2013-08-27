<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Form\Subscriber;

use Pim\Bundle\ProductBundle\Form\Subscriber\TransformImportedProductDataSubscriber;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TransformImportedProductDataSubscriberTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->familyRepository = $this->getRepositoryMock();

        $em = $this->getEntityManagerMock();
        $em
            ->expects($this->any())
            ->method('getRepository')
            ->will(
                $this->returnValueMap(
                    array(
                        array('PimProductBundle:Family', $this->familyRepository),
                    )
                )
            );

        $this->subscriber = new TransformImportedProductDataSubscriber($em);
        $this->form = $this->getFormMock();
    }

    public function testSubscribedEvent()
    {
        $this->assertEquals(
            array(FormEvents::PRE_SUBMIT => 'preSubmit'),
            TransformImportedProductDataSubscriber::getSubscribedEvents()
        );
    }

    public function testEnableImportedProduct()
    {
        $event = new FormEvent($this->form, array());

        $this->subscriber->setProductEnabled(true);

        $this->subscriber->preSubmit($event);

        $data = $event->getData();
        $this->assertArrayHasKey('enabled', $data);
        $this->assertTrue($data['enabled']);
    }

    public function testDisabledImportedProduct()
    {
        $event = new FormEvent($this->form, array());

        $this->subscriber->setProductEnabled(false);

        $this->subscriber->preSubmit($event);

        $data = $event->getData();
        $this->assertArrayHasKey('enabled', $data);
        $this->assertFalse($data['enabled']);
    }

    public function testIgnoreUnsetProductProperties()
    {
        $event = new FormEvent($this->form, array());

        $this->subscriber->preSubmit($event);

        $data = $event->getData();
        $this->assertArrayNotHasKey('enabled', $data);
        $this->assertArrayNotHasKey('family', $data);
    }

    public function testSetImportedProductFamily()
    {
        $event = new FormEvent($this->form, array('family' => 'furniture'));

        $this->subscriber->setFamilyKey('family');

        $this->familyRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(array('code' => 'furniture'))
            ->will($this->returnValue($this->getFamilyMock(1987)));

        $this->subscriber->preSubmit($event);

        $data = $event->getData();
        $this->assertArrayHasKey('family', $data);
        $this->assertEquals(1987, $data['family']);
    }

    public function testIgnoreUnknownImportedProductFamily()
    {
        $event = new FormEvent($this->form, array('family' => 'furniture'));

        $this->subscriber->setFamilyKey('family');

        $this->familyRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue(null));

        $this->subscriber->preSubmit($event);

        $data = $event->getData();
        $this->assertArrayNotHasKey('family', $data);
    }

    private function getFormMock()
    {
        return $this
            ->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getEntityManagerMock()
    {
        return $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getRepositoryMock()
    {
        return $this
            ->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getFamilyMock($id)
    {
        $family = $this->getMock('Pim\Bundle\ProductBundle\Entity\Family');

        $family->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));

        return $family;
    }
}
