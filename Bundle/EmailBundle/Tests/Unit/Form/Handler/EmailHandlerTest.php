<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Form\Handler;

use Oro\Bundle\EmailBundle\Entity\EmailBody;
use Oro\Bundle\EmailBundle\Entity\EmailFolder;
use Oro\Bundle\EmailBundle\Entity\InternalEmailOrigin;
use Oro\Bundle\EmailBundle\Entity\Email as EmailEntity;
use Oro\Bundle\EmailBundle\Tests\Unit\Entity\TestFixtures\EmailAddress;
use Oro\Bundle\EmailBundle\Tests\Unit\Fixtures\Entity\TestUser;
use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\EmailBundle\Form\Handler\EmailHandler;
use Oro\Bundle\EmailBundle\Form\Model\Email;

class EmailHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $em;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $translator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $securityContext;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $emailAddressManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $emailEntityBuilder;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mailer;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $nameFormatter;

    /** @var Email */
    protected $model;

    /** @var EmailHandler */
    protected $handler;

    protected function setUp()
    {
        $this->form                = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request             = new Request();
        $this->em                  = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->translator = $this->getMockBuilder('Symfony\Component\Translation\Translator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->securityContext     = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->emailAddressManager = $this->getMockBuilder('Oro\Bundle\EmailBundle\Entity\Manager\EmailAddressManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->emailEntityBuilder  = $this->getMockBuilder('Oro\Bundle\EmailBundle\Builder\EmailEntityBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mailer              = $this->getMockBuilder('\Swift_Mailer')
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger              = $this->getMock('Psr\Log\LoggerInterface');
        $this->nameFormatter       = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Formatter\NameFormatter')
            ->disableOriginalConstructor()
            ->getMock();

        $emailAddressRepository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $emailAddressRepository->expects($this->any())
            ->method('findOneBy')
            ->will(
                $this->returnCallback(
                    function ($args) {
                        $emailAddress = new EmailAddress();
                        $emailAddress->setEmail($args['email']);
                        $emailAddress->setOwner(new TestUser($args['email'], 'FirstName', 'LastName'));

                        return $emailAddress;
                    }
                )
            );
        $this->emailAddressManager->expects($this->any())
            ->method('getEmailAddressRepository')
            ->with($this->identicalTo($this->em))
            ->will($this->returnValue($emailAddressRepository));

        $this->model   = new Email();
        $this->handler = new EmailHandler(
            $this->form,
            $this->request,
            $this->em,
            $this->translator,
            $this->securityContext,
            $this->emailAddressManager,
            $this->emailEntityBuilder,
            $this->mailer,
            $this->logger,
            $this->nameFormatter
        );
    }

    public function testProcessGetRequestWithEmptyQueryString()
    {
        $this->request->setMethod('GET');

        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->model);

        $this->form->expects($this->never())
            ->method('submit');

        $user  = new TestUser('test@example.com', 'John', 'Smith');

        $this->nameFormatter->expects($this->any())
            ->method('format')
            ->with($user)
            ->will($this->returnValue('John Smith'));

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($user));
        $this->securityContext->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($token));

        $this->assertFalse($this->handler->process($this->model));

        $this->assertEquals(null, $this->model->getGridName());
        $this->assertEquals('John Smith <test@example.com>', $this->model->getFrom());
    }

    public function testProcessGetRequest()
    {
        $this->request->setMethod('GET');
        $this->request->query->set('gridName', 'testGrid');
        $this->request->query->set('from', 'from@example.com');
        $this->request->query->set('to', 'to@example.com');
        $this->request->query->set('subject', 'testSubject');

        $this->nameFormatter->expects($this->any())
            ->method('format')
            ->with($this->isInstanceOf('Oro\Bundle\EmailBundle\Tests\Unit\Fixtures\Entity\TestUser'))
            ->will($this->returnValue('FirstName LastName'));

        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->model);

        $this->form->expects($this->never())
            ->method('submit');

        $this->assertFalse($this->handler->process($this->model));

        $this->assertEquals('testGrid', $this->model->getGridName());
        $this->assertEquals('FirstName LastName <from@example.com>', $this->model->getFrom());
        $this->assertEquals(array('FirstName LastName <to@example.com>'), $this->model->getTo());
        $this->assertEquals('testSubject', $this->model->getSubject());
    }

    /**
     * @dataProvider supportedMethods
     */
    public function testProcessValidData($method)
    {
        $this->request->setMethod($method);
        $this->model
            ->setFrom('from@example.com')
            ->setTo(array('to@example.com'))
            ->setSubject('testSubject')
            ->setBody('testBody');

        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->model);

        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);

        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $message = new \Swift_Message();
        $this->mailer->expects($this->once())
            ->method('createMessage')
            ->will($this->returnValue($message));
        $this->mailer->expects($this->once())
            ->method('send')
            ->will($this->returnValue(1));

        $origin = new InternalEmailOrigin();
        $folder = new EmailFolder();
        $folder->setType(EmailFolder::SENT);
        $origin->addFolder($folder);
        $emailOriginRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $emailOriginRepo->expects($this->once())
            ->method('findOneBy')
            ->with(array('name' => InternalEmailOrigin::BAP))
            ->will($this->returnValue($origin));
        $this->em->expects($this->once())
            ->method('getRepository')
            ->with('OroEmailBundle:InternalEmailOrigin')
            ->will($this->returnValue($emailOriginRepo));

        $this->emailEntityBuilder->expects($this->once())
            ->method('setOrigin')
            ->with($this->identicalTo($origin));
        $email = new EmailEntity();
        $this->emailEntityBuilder->expects($this->once())
            ->method('email')
            ->with('testSubject', 'from@example.com', array('to@example.com'))
            ->will($this->returnValue($email));
        $body = new EmailBody();
        $this->emailEntityBuilder->expects($this->once())
            ->method('body')
            ->with('testBody', false, true)
            ->will($this->returnValue($body));
        $batch = $this->getMock('Oro\Bundle\EmailBundle\Builder\EmailEntityBatchInterface');
        $this->emailEntityBuilder->expects($this->once())
            ->method('getBatch')
            ->will($this->returnValue($batch));
        $batch->expects($this->once())
            ->method('persist')
            ->with($this->identicalTo($this->em));
        $this->em->expects($this->once())
            ->method('flush');

        $this->assertTrue($this->handler->process($this->model));

        $this->assertNotNull($message);
        $this->assertEquals(array('from@example.com' => null), $message->getFrom());
        $this->assertEquals(array('to@example.com' => null), $message->getTo());
        $this->assertEquals('testSubject', $message->getSubject());
        $this->assertEquals('testBody', $message->getBody());

        $this->assertTrue($folder === $email->getFolder());
        $this->assertTrue($body === $email->getEmailBody());
    }

    public function supportedMethods()
    {
        return array(
            array('POST'),
            //array('PUT')
        );
    }
}
