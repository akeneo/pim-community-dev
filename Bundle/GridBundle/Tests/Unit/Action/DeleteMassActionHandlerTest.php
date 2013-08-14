<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action;

use Oro\Bundle\GridBundle\Action\MassAction\DeleteMassActionHandler;

class DeleteMassActionHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var DeleteMassActionHandler */
    protected $handler;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $em;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $translator;

    /**
     * setup test mocks
     */
    public function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');

        $this->handler = new DeleteMassActionHandler($this->em, $this->translator);
    }

    public function testHandle()
    {
        
    }
}
