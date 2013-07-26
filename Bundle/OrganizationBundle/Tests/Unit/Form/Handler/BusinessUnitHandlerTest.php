<?php
namespace Oro\Bundle\OrganizationBundle\Tests\Unit\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\UserBundle\Entity\User;

use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Form\Handler\BusinessUnitHandler;

class BusinessUnitHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FormInterface
     */
    protected $form;

    /**
     * @var BusinessUnitHandler
     */
    protected $handler;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ObjectManager
     */
    protected $manager;

    /**
     * @var BusinessUnit
     */
    protected $entity;

    protected function setUp()
    {
        $this->manager = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = new Request();
        $this->form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->entity  = new BusinessUnit();
        $this->handler = new BusinessUnitHandler($this->form, $this->request, $this->manager);
    }

    public function testProcessValidData()
    {
        $appendedUser = new User();
        $appendedUser->setId(1);

        $removedUser = new User();
        $removedUser->setId(2);

        $removedUser->addBusinessUnit($this->entity);

        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);

        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);

        $this->request->setMethod('POST');

        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $appendForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $appendForm->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(array($appendedUser)));
        $this->form->expects($this->at(3))
            ->method('get')
            ->with('appendUsers')
            ->will($this->returnValue($appendForm));

        $removeForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $removeForm->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(array($removedUser)));
        $this->form->expects($this->at(4))
            ->method('get')
            ->with('removeUsers')
            ->will($this->returnValue($removeForm));

        $this->manager->expects($this->at(0))
            ->method('persist')
            ->with($appendedUser);

        $this->manager->expects($this->at(1))
            ->method('persist')
            ->with($removedUser);

        $this->manager->expects($this->at(2))
            ->method('persist')
            ->with($this->entity);

        $this->manager->expects($this->once())
            ->method('flush');

        $this->assertTrue($this->handler->process($this->entity));

        $businessUnits = $appendedUser->getBusinessUnits()->toArray();
        $this->assertCount(1, $businessUnits);
        $this->assertEquals($this->entity, current($businessUnits));
        $this->assertCount(0, $removedUser->getBusinessUnits()->toArray());
    }

    public function testBadMethod()
    {
        $this->request->setMethod('GET');
        $this->assertFalse($this->handler->process($this->entity));
    }
}
