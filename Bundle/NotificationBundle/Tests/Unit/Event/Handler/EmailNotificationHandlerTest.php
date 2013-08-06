<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Event\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\NotificationBundle\Event\Handler\EmailNotificationHandler;
use Monolog\Logger;
use Symfony\Component\Security\Core\SecurityContextInterface;

class EmailNotificationHandlerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_ENTITY_CLASS = 'SomeEntity';

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $entityManager;

    /** @var \Twig_Environment */
    protected $twig;

    /** @var \Swift_Mailer */
    protected $mailer;

    /** @var EmailNotificationHandler */
    protected $handler;

    /** @var Logger */
    protected $logger;

    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $cache;

    /** @var \PHPUnit_Framework_MockObject_MockObject  */
    protected $configProvider;

    /** @var \PHPUnit_Framework_MockObject_MockObject  */
    protected $securityPolicy;

    /** @var \PHPUnit_Framework_MockObject_MockObject  */
    protected $sandbox;

    /** @var string */
    protected $cacheKey = 'test.key';

    protected function setUp()
    {
        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();

        $this->twig = $this->getMockBuilder('\Twig_Environment')
            ->disableOriginalConstructor()->getMock();

        $this->securityPolicy = $this->getMockBuilder('\Twig_Sandbox_SecurityPolicy')
            ->disableOriginalConstructor()->getMock();

        $this->sandbox = $this->getMockBuilder('\Twig_Extension_Sandbox')
            ->disableOriginalConstructor()->getMock();

        $this->twig->expects($this->once())->method('getExtension')->with('sandbox')
            ->will($this->returnValue($this->sandbox));

        $this->sandbox->expects($this->once())->method('getSecurityPolicy')
            ->will($this->returnValue($this->securityPolicy));

        $this->mailer = $this->getMockBuilder('\Swift_Mailer')
            ->disableOriginalConstructor()->getMock();

        $this->logger = $this->getMockBuilder('Monolog\Logger')
            ->disableOriginalConstructor()->getMock();

        $this->securityContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');

        $this->configProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()->getMock();

        $this->cache = $this->getMockBuilder('Doctrine\Common\Cache\Cache')
            ->disableOriginalConstructor()->getMock();

        $this->cache->expects($this->once())->method('fetch')
            ->will($this->returnValue(serialize(array('somekey' => array()))));

        $this->handler = new EmailNotificationHandler(
            $this->twig,
            $this->mailer,
            $this->entityManager,
            'a@a.com',
            $this->logger,
            $this->securityContext,
            $this->configProvider,
            $this->cache,
            $this->cacheKey
        );
        $this->handler->setEnv('prod');
        $this->handler->setMessageLimit(10);
    }

    protected function tearDown()
    {
        unset($this->entityManager);
        unset($this->twig);
        unset($this->securityPolicy);
        unset($this->sandbox);
        unset($this->mailer);
        unset($this->logger);
        unset($this->securityContext);
        unset($this->configProvider);
        unset($this->cache);
        unset($this->handler);
    }

    /**
     * Test handler
     */
    public function testHandle()
    {
        $entity = $this->getMock('Oro\Bundle\TagBundle\Entity\ContainAuthorInterface');
        $event = $this->getMock('Oro\Bundle\NotificationBundle\Event\NotificationEvent', array(), array($entity));
        $event->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($entity));

        $templateContent = "@subject = Test Subj\n@entityName = TestEntity";

        $template = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailTemplate');
        $template->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($templateContent));
        $template->expects($this->exactly(2))
            ->method('getType')
            ->will($this->returnValue('html'));
        $template->expects($this->once())
            ->method('getSubject')
            ->will($this->returnValue('Test Subj'));

        $notification = $this->getMock('Oro\Bundle\NotificationBundle\Entity\EmailNotification');
        $notification->expects($this->once())
            ->method('getTemplate')
            ->will($this->returnValue($template));

        $recipientList = $this->getMock('Oro\Bundle\NotificationBundle\Entity\RecipientList');
        $notification->expects($this->once())
            ->method('getRecipientList')
            ->will($this->returnValue($recipientList));

        $notifications = array(
            $notification,
        );

        $entity = $this->getMock('Oro\Bundle\TagBundle\Entity\ContainAuthorInterface');

        $repo = $this->getMockBuilder('Oro\Bundle\NotificationBundle\Entity\Repository\RecipientListRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('getRecipientEmails')
            ->with($recipientList, $entity)
            ->will($this->returnValue(array('a@aa.com')));

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with('Oro\Bundle\NotificationBundle\Entity\RecipientList')
            ->will($this->returnValue($repo));

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->isInstanceOf('\Swift_Message'));

        $this->addJob();

        $this->handler->handle($event, $notifications);
    }

    /**
     * Test handler with expection and empty recipients
     */
    public function testHandleErrors()
    {
        $entity = $this->getMock('Oro\Bundle\TagBundle\Entity\ContainAuthorInterface');
        $event = $this->getMock('Oro\Bundle\NotificationBundle\Event\NotificationEvent', array(), array($entity));
        $event->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($entity));

        $templateContent = "@subject = Test Subj\n@entityName = TestEntity";
        $template = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailTemplate');
        $template->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($templateContent));
        $template->expects($this->once())
            ->method('getType')
            ->will($this->returnValue('html'));

        $notification = $this->getMock('Oro\Bundle\NotificationBundle\Entity\EmailNotification');
        $notification->expects($this->once())
            ->method('getTemplate')
            ->will($this->returnValue($template));

        $recipientList = $this->getMock('Oro\Bundle\NotificationBundle\Entity\RecipientList');
        $notification->expects($this->once())
            ->method('getRecipientList')
            ->will($this->returnValue($recipientList));

        $notifications = array(
            $notification,
        );

        $this->twig->expects($this->once())
            ->method('render')
            ->will($this->throwException(new \Twig_Error('bla bla bla')));

        $entity = $this->getMock('Oro\Bundle\TagBundle\Entity\ContainAuthorInterface');

        $repo = $this->getMockBuilder('Oro\Bundle\NotificationBundle\Entity\Repository\RecipientListRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('getRecipientEmails')
            ->with($recipientList, $entity)
            ->will($this->returnValue(array()));

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with('Oro\Bundle\NotificationBundle\Entity\RecipientList')
            ->will($this->returnValue($repo));

        $this->handler->handle($event, $notifications);
    }

    public function testNotify()
    {
        $params = $this->getMock('Symfony\Component\HttpFoundation\ParameterBag');
        $params->expects($this->once())
            ->method('get')
            ->with('to')
            ->will($this->returnValue(array()));

        $this->assertFalse($this->handler->notify($params));
    }

    /**
     * add job assertions
     */
    public function addJob()
    {
        $query = $this->getMock(
            'Doctrine\ORM\AbstractQuery',
            array('getSQL', 'setMaxResults', 'getOneOrNullResult', 'setParameter', '_doExecute'),
            array(),
            '',
            false
        );

        $query->expects($this->once())->method('getOneOrNullResult')
            ->will($this->returnValue(null));
        $query->expects($this->exactly(2))->method('setParameter')
            ->will($this->returnSelf());
        $query->expects($this->once())
            ->method('setMaxResults')
            ->with($this->equalTo(1))
            ->will($this->returnSelf());

        $this->entityManager->expects($this->once())
            ->method('createQuery')
            ->will($this->returnValue($query));

        $this->entityManager->expects($this->once())
            ->method('persist');
        $this->entityManager->expects($this->once())
            ->method('flush');
    }

    public function testPrepareConfiguration()
    {
        $configuredData = array(
            self::TEST_ENTITY_CLASS => array(
                'getsomecode'
            )
        );

        $newcache = $this->getMockBuilder('Doctrine\Common\Cache\Cache')
            ->disableOriginalConstructor()->getMock();
        $newcache->expects($this->once())->method('fetch')
            ->will($this->returnValue(false));
        $newcache->expects($this->once())->method('save')
            ->with($this->cacheKey, serialize($configuredData));

        $twig = $this->getMockBuilder('\Twig_Environment')
            ->disableOriginalConstructor()->getMock();

        $securityPolicy = $this->getMockBuilder('\Twig_Sandbox_SecurityPolicy')
            ->disableOriginalConstructor()->getMock();

        $sandbox = $this->getMockBuilder('\Twig_Extension_Sandbox')
            ->disableOriginalConstructor()->getMock();

        $twig->expects($this->once())->method('getExtension')->with('sandbox')
            ->will($this->returnValue($sandbox));

        $sandbox->expects($this->once())->method('getSecurityPolicy')
            ->will($this->returnValue($securityPolicy));

        $this->configProvider->expects($this->once())->method('getAllConfigurableEntityNames')
            ->will($this->returnValue(array(self::TEST_ENTITY_CLASS)));

        $fieldsCollection = new ArrayCollection();

        $config = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\EntityConfig')
            ->disableOriginalConstructor()->getMock();
        $config->expects($this->once())->method('getFields')
            ->will(
                $this->returnCallback(
                    function ($callback) use ($fieldsCollection) {
                        return $fieldsCollection->filter($callback);
                    }
                )
            );

        $this->configProvider->expects($this->once())->method('getConfig')->with(self::TEST_ENTITY_CLASS)
            ->will($this->returnValue($config));

        $field1 = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\FieldConfig')
            ->disableOriginalConstructor()->getMock();
        $field2 = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\FieldConfig')
            ->disableOriginalConstructor()->getMock();

        $field1->expects($this->once())->method('is')->with('available_in_template')
            ->will($this->returnValue(true));
        $field1->expects($this->once())->method('getCode')
            ->will($this->returnValue('someCode'));

        $field2->expects($this->once())->method('is')->with('available_in_template')
            ->will($this->returnValue(false));

        $fieldsCollection->add($field1);
        $fieldsCollection->add($field2);

        new EmailNotificationHandler(
            $twig,
            $this->mailer,
            $this->entityManager,
            'a@a.com',
            $this->logger,
            $this->securityContext,
            $this->configProvider,
            $newcache,
            $this->cacheKey
        );
    }
}
