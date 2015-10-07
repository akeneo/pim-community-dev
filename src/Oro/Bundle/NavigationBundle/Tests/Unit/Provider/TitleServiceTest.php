<?php
namespace Oro\Bundle\NavigationBundle\Tests\Unit\Provider;

use Oro\Bundle\NavigationBundle\Provider\TitleService;

class TitleServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $annotationsReader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configReader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $em;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $translator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $serializer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $repository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $breadcrumbManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $userConfigManager;

    /**
     * @var TitleService
     */
    private $titleService;

    protected function setUp()
    {
        $this->annotationsReader =
            $this->getMockBuilder('Oro\Bundle\NavigationBundle\Title\TitleReader\AnnotationsReader')
                ->disableOriginalConstructor()
                ->getMock();

        $this->configReader = $this->getMockBuilder('Oro\Bundle\NavigationBundle\Title\TitleReader\ConfigReader')
            ->disableOriginalConstructor()
            ->getMock();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->translator = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Translation\Translator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializer = $this->getMockBuilder('JMS\Serializer\Serializer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->userConfigManager = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Config\UserConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->breadcrumbManager = $this->getMockBuilder('Oro\Bundle\NavigationBundle\Menu\BreadcrumbManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->titleService = new TitleService(
            $this->annotationsReader,
            $this->configReader,
            $this->translator,
            $this->em,
            $this->serializer,
            $this->userConfigManager,
            $this->breadcrumbManager
        );
    }

    public function testRender()
    {
        $this->translator->expects($this->exactly(3))
            ->method('trans');

        $result = $this->titleService->render(array(), null, 'Prefix', 'Suffix');

        $this->assertTrue(is_string($result));
    }

    public function testRenderStored()
    {
        $data = 'test data';

        $storedTitleMock = $this->getMock('Oro\Bundle\NavigationBundle\Title\StoredTitle');

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with($data, 'Oro\Bundle\NavigationBundle\Title\StoredTitle', 'json')
            ->will($this->returnValue($storedTitleMock));

        $storedTitleMock->expects($this->once())
            ->method('getParams');

        $storedTitleMock->expects($this->once())
            ->method('getTemplate')
            ->will($this->returnValue('string'));

        $storedTitleMock->expects($this->once())
            ->method('getPrefix');

        $storedTitleMock->expects($this->once())
            ->method('getSuffix');

        $result = $this->titleService->render(array(), $data, null, null, true);

        $this->assertTrue(is_string($result));
    }

    public function testRenderShort()
    {
        $shortTitle = 'short title';
        $this->translator->expects($this->exactly(1))
            ->method('trans')
            ->with($this->equalTo($shortTitle))
            ->will($this->returnValue($shortTitle));
        $this->titleService->setShortTemplate($shortTitle);
        $result = $this->titleService->render(array(), null, 'Prefix', 'Suffix', true, true);
        $this->assertTrue(is_string($result));
        $this->assertEquals($result, $shortTitle);
    }

    public function testSettersAndGetters()
    {
        $testString = 'Test string';
        $testArray = array('test');

        $this->assertInstanceOf(
            '\Oro\Bundle\NavigationBundle\Provider\TitleService',
            $this->titleService->setSuffix($testString)
        );
        $this->assertInstanceOf(
            '\Oro\Bundle\NavigationBundle\Provider\TitleService',
            $this->titleService->setPrefix($testString)
        );

        $this->titleService->setParams($testArray);
        $this->assertEquals($testArray, $this->titleService->getParams());

        $dataArray = array(
            'titleTemplate' => 'titleTemplate',
            'titleShortTemplate' => 'titleShortTemplate',
            'prefix' => 'prefix',
            'suffix' => 'suffix',
            'params' => array('test_params')
        );
        $this->titleService->setData($dataArray);

        $this->assertEquals($dataArray['titleTemplate'], $this->titleService->getTemplate());
        $this->assertEquals($dataArray['titleShortTemplate'], $this->titleService->getShortTemplate());
        $this->assertEquals($dataArray['params'], $this->titleService->getParams());
    }

    /**
     * Tests case with exists item
     */
    public function testLoadByRouteExistInDB()
    {
        $route = 'test_route';

        $this->em->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($this->repository));

        $entityMock = $this->getMock('Oro\Bundle\NavigationBundle\Entity\Title');

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(array('route' => $route)))
            ->will($this->returnValue($entityMock));

        $testTitle = 'Test title';
        $entityMock->expects($this->once())
            ->method('getTitle')
            ->will($this->returnValue($testTitle));

        $this->titleService->loadByRoute($route);

        $this->assertEquals($testTitle, $this->titleService->getTemplate());
    }

    /**
     * Tests case with doesn't exists item
     */
    public function testLoadByRouteDoesntExistInDB()
    {
        $route = 'test_route';

        $this->em->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($this->repository));

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(array('route' => $route)))
            ->will($this->returnValue(false));

        $this->titleService->loadByRoute($route);
    }

    /**
     * Tests case with fallback to config value
     */
    public function testLoadByRouteFallbackToConfig()
    {
        $route = 'test_route';

        $this->em->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($this->repository));

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(array('route' => $route)))
            ->will($this->returnValue(false));

        $titles = array($route => 'Test title template %placeholder%');
        $this->titleService->setTitles($titles);
        $this->titleService->loadByRoute($route);

        $this->assertEquals($titles[$route], $this->titleService->getTemplate());
    }

    /**
     * Prepare readers for update test
     */
    private function prepareReaders()
    {
        $this->annotationsReader->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(array()));

        $this->configReader->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(array()));
    }

    public function testRemoveItemsDuringUpdate()
    {
        $this->prepareReaders();

        $this->em->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($this->repository));

        $entityMock = $this->getMock('Oro\Bundle\NavigationBundle\Entity\Title');

        $entityMock->expects($this->once())
            ->method('getRoute')
            ->will($this->returnValue('test_route'));

        $this->repository->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue(array($entityMock)));

        $this->em->expects($this->once())
            ->method('remove');

        $this->em->expects($this->once())
            ->method('flush');

        $this->titleService->update(array());
    }

    public function testUpdateItemsDuringUpdate()
    {
        $this->prepareReaders();

        $testData = array('route_name' => 'Title');

        $this->em->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($this->repository));

        $this->userConfigManager->expects($this->at(0))->method('get')
            ->with('oro_navigation.breadcrumb_menu')->will($this->returnValue('test-menu'));
        $this->userConfigManager->expects($this->at(1))->method('get')
            ->with('oro_navigation.title_suffix')->will($this->returnValue('test-suffix'));
        $this->userConfigManager->expects($this->at(2))->method('get')
            ->with('oro_navigation.title_delimiter')->will($this->returnValue('/'));

        $entityMock = $this->getMock('Oro\Bundle\NavigationBundle\Entity\Title');

        $entityMock->expects($this->exactly(2))
            ->method('getRoute')
            ->will($this->returnValue('route_name'));

        $entityMock->expects($this->once())
            ->method('getIsSystem')
            ->will($this->returnValue(true));

        $entityMock->expects($this->once())
            ->method('setTitle')
            ->with($this->equalTo('Title / test-breadcrumb / test-suffix'));

        $entityMock->expects($this->once())
            ->method('setShortTitle')
            ->with($this->equalTo('Title'));

        $this->repository->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue(array($entityMock)));

        $this->em->expects($this->once())
            ->method('persist');

        $this->em->expects($this->once())
            ->method('flush');

        $this->breadcrumbManager->expects($this->once())
            ->method('getBreadcrumbLabels')
            ->will($this->returnValue(array('test-breadcrumb')));

        $this->titleService->update($testData);
    }

    public function testInsertItemsDuringUpdate()
    {
        $this->prepareReaders();

        $testData = array('route_name' => 'Title');

        $this->em->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($this->repository));

        $this->repository->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue(array()));

        $this->em->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf('Oro\Bundle\NavigationBundle\Entity\Title'));

        $this->em->expects($this->once())
            ->method('flush');

        $this->titleService->update($testData);
    }

    public function testGetSerialized()
    {
        $testValue = 'test value';

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($this->isInstanceOf('\Oro\Bundle\NavigationBundle\Title\StoredTitle'), $this->equalTo('json'))
            ->will($this->returnValue($testValue));

        $result = $this->titleService->getSerialized();

        $this->assertEquals($testValue, $result);
    }
}
