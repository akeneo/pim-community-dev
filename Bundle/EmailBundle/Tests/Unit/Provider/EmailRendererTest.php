<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\EmailBundle\Provider\EmailRenderer;
use Symfony\Component\Security\Core\SecurityContextInterface;

class EmailRendererTest extends \PHPUnit_Framework_TestCase
{
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

    /** @var EmailRenderer */
    protected $renderer = 'test.key';

    /** @var \PHPUnit_Framework_MockObject_MockObject  */
    protected $user;

    /**
     * setup mocks
     */
    protected function setUp()
    {
        $this->loader = $this->getMock('\Twig_Loader_String');

        $this->securityPolicy = $this->getMockBuilder('\Twig_Sandbox_SecurityPolicy')
            ->disableOriginalConstructor()->getMock();

        $this->sandbox = $this->getMockBuilder('\Twig_Extension_Sandbox')
            ->disableOriginalConstructor()
            ->getMock();

        $this->sandbox->expects($this->once())->method('getName')
            ->will($this->returnValue('sandbox'));
        $this->sandbox->expects($this->once())->method('getSecurityPolicy')
            ->will($this->returnValue($this->securityPolicy));

        $this->securityContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');

        $token = $this->getMockForAbstractClass(
            'Symfony\Component\Security\Core\Authentication\Token\TokenInterface'
        );
        $this->user = $this->getMockBuilder('Oro\Bundle\UserBundle\Entity\User')
            ->disableOriginalConstructor()->getMock();
        $token->expects($this->any())->method('getUser')
            ->will($this->returnValue($this->user));
        $this->securityContext->expects($this->any())->method('getToken')
            ->will($this->returnValue($token));


        $this->configProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()->getMock();

        $this->cache = $this->getMockBuilder('Doctrine\Common\Cache\Cache')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * test configureSandbox method
     */
    public function testConfigureSandboxCached()
    {
        $this->cache
            ->expects($this->once())
            ->method('fetch')
            ->with($this->cacheKey)
            ->will($this->returnValue(serialize(array('somekey' => array()))));

        $this->getRendererInstance();
    }

    /**
     * configureSanbox method with not cached scenario
     */
    public function testConfigureSandboxNotCached()
    {
        $entityClass = 'Oro\Bundle\UserBundle\Entity\User';

        $configIdMock = $this->getMockForAbstractClass('Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface');
        $configIdMock
            ->expects($this->once())->method('getClassName')
            ->will($this->returnValue($entityClass));

        $configuredData = array(
            $entityClass => array(
                'getsomecode'
            )
        );

        $this->cache
            ->expects($this->once())
            ->method('fetch')
            ->with($this->cacheKey)
            ->will($this->returnValue(false));

        $this->cache
            ->expects($this->once())
            ->method('save')
            ->with($this->cacheKey, serialize($configuredData));

        $configurableEntities = array($configIdMock);
        $this->configProvider
            ->expects($this->once())
            ->method('getIds')
            ->will($this->returnValue($configurableEntities));

        $fieldsCollection = new ArrayCollection();

        $this->configProvider->expects($this->once())->method('filter')
            ->will(
                $this->returnCallback(
                    function ($callback) use ($fieldsCollection) {
                        return $fieldsCollection->filter($callback);
                    }
                )
            );

        $field1Id = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId')
            ->disableOriginalConstructor()
            ->getMock();
        $field1Id->expects($this->once())
            ->method('getFieldName')
            ->will($this->returnValue('someCode'));

        $field1 = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $field2 = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $field1->expects($this->once())
            ->method('is')
            ->with('available_in_template')
            ->will($this->returnValue(true));
        $field1->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($field1Id));

        $field2->expects($this->once())
            ->method('is')
            ->with('available_in_template')
            ->will($this->returnValue(false));

        $fieldsCollection->add($field1);
        $fieldsCollection->add($field2);

        $this->getRendererInstance();
    }

    /**
     * Compile message test
     */
    public function testCompileMessage()
    {
        $this->cache
            ->expects($this->once())
            ->method('fetch')
            ->with($this->cacheKey)
            ->will($this->returnValue(serialize(array('somekey' => array()))));

        $content = 'test content <a href="sdfsdf">asfsdf</a> {{ entity.name }}';
        $subject = 'subject';

        $emailTemplate = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailTemplate');
        $emailTemplate->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($content));
        $emailTemplate->expects($this->once())
            ->method('getType')
            ->will($this->returnValue('txt'));
        $emailTemplate->expects($this->once())
            ->method('getSubject')
            ->will($this->returnValue($subject));

        $entity = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
        $templateParams = array(
            'entity' => $entity,
        );

        $renderer = $this->getRendererInstance();

        $renderer->expects($this->at(0))
            ->method('render')
            ->with(
                strip_tags($content),
                array_merge($templateParams, array('user' => $this->user))
            );
        $renderer->expects($this->at(1))
            ->method('render')
            ->with(
                $subject,
                array_merge($templateParams, array('user' => $this->user))
            );

        $result = $renderer->compileMessage($emailTemplate, $templateParams);

        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
    }


    /**
     * Compile template preview test
     */
    public function testCompilePreview()
    {
        $this->cache
            ->expects($this->once())
            ->method('fetch')
            ->with($this->cacheKey)
            ->will($this->returnValue(serialize(array('somekey' => array()))));

        $content = 'test content <a href="sdfsdf">asfsdf</a> {{ entity.name }}';

        $emailTemplate = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailTemplate');
        $emailTemplate->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($content));
        $emailTemplate->expects($this->once())
            ->method('getType')
            ->will($this->returnValue('html'));

        $renderer = $this->getRendererInstance();

        $renderer->expects($this->at(0))
            ->method('render')
            ->with('{% verbatim %}' . $content . '{% endverbatim %}');
        $renderer->compilePreview($emailTemplate);
    }

    /**
     * @return EmailRenderer
     */
    public function getRendererInstance()
    {
        return $this->getMock(
            'Oro\Bundle\EmailBundle\Provider\EmailRenderer',
            array('render'),
            array(
                $this->loader,
                array(),
                $this->configProvider,
                $this->cache,
                $this->cacheKey,
                $this->securityContext,
                $this->sandbox
            )
        );
    }
}
