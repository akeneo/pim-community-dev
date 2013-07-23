<?php
namespace Oro\Bundle\OrganizationBundle\Tests\Unit\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;

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

    protected $tagManager;

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
        $this->tagManager = $this->getMockBuilder('Oro\Bundle\TagBundle\Entity\TagManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->entity  = new BusinessUnit();
        $this->handler = new BusinessUnitHandler($this->form, $this->request, $this->manager);
        $this->handler->setTagManager($this->tagManager);
    }

    public function testProcessValidData()
    {
        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);

        $this->tagManager->expects($this->once())
            ->method('saveTagging');

        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);

        $this->request->setMethod('POST');

        $this->manager->expects($this->once())
            ->method('persist')
            ->with($this->entity);

        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->manager->expects($this->once())
            ->method('flush');

        $this->assertTrue($this->handler->process($this->entity));
    }

    public function testBadMethod()
    {
        $this->request->setMethod('GET');
        $this->assertFalse($this->handler->process($this->entity));
    }
}
