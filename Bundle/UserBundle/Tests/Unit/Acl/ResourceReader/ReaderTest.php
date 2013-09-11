<?php
namespace Oro\Bundle\UserBundle\Tests\Unit\Acl\ResourceReader;

use Oro\Bundle\UserBundle\Acl\ResourceReader\Reader;
use Oro\Bundle\SecurityBundle\Annotation\Acl;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Oro\Bundle\UserBundle\Acl\ResourceReader\Reader
     */
    private $reader;

    private $kernelMoc;

    private $annotationReader;

    private $testBundle;

    public function setUp()
    {
        if (!interface_exists('Doctrine\Common\Annotations\Reader')) {
            $this->markTestSkipped('Doctrine Common has to be installed for this test to run.');
        }

        $this->testBundle = $this->getMock(
            'Symfony\Bundle\FrameworkBundle\FrameworkBundle'
        );

        $this->kernelMoc = $this->getMockForAbstractClass(
            'Symfony\Component\HttpKernel\KernelInterface',
            array()
        );

        $this->annotationReader = $this->getMock(
            'Doctrine\Common\Annotations\AnnotationReader'
        );

        $this->reader = new Reader($this->kernelMoc, $this->annotationReader);
    }

    public function testGetResources()
    {
        $acl = new Acl(
            array(
                'id' => 'test_acl',
                'name' => 'name acl',
                'description' => 'test description'
            )
        );
        $aclMethod = new Acl(
            array(
                 'id' => 'test_acl_method',
                 'name' => 'name acl method',
                 'description' => 'test method description'
            )
        );
        $this->annotationReader->expects($this->any())
            ->method('getClassAnnotation')
            ->will($this->returnValue($acl));

        $this->annotationReader->expects($this->any())
            ->method('getMethodAnnotation')
            ->will($this->returnValue($aclMethod));

        $this->kernelMoc->expects($this->once())
            ->method('getBundles')
            ->will($this->returnValue(array($this->testBundle)));

        $this->testBundle->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue(realpath(__DIR__ . '/../../../Unit/Fixture/Controller')));

        $resultAclList = $this->reader->getResources();

        $this->assertEquals(2, count($resultAclList));

        $this->assertEquals(true, isset($resultAclList['test_acl']));
        $controllerAcl = $resultAclList['test_acl'];
        $this->assertEquals(false, $controllerAcl->getParent());
    }

    public function testGetResourcesWoDirectories()
    {
        $this->kernelMoc->expects($this->once())
            ->method('getBundles')
            ->will($this->returnValue(array()));

        $resultAclList = $this->reader->getResources();
        $this->assertEquals(0, count($resultAclList));
    }

    public function testFilesWoClass()
    {
        $this->kernelMoc->expects($this->once())
            ->method('getBundles')
            ->will($this->returnValue(array($this->testBundle)));
        $this->testBundle->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue(realpath(__DIR__ . '/../../../Unit/Fixture/FileWoClass')));
        $this->setExpectedException('RuntimeException');
        $this->reader->getResources();
    }

    public function testFilesWoNamespace()
    {
        $this->kernelMoc->expects($this->once())
            ->method('getBundles')
            ->will($this->returnValue(array($this->testBundle)));
        $this->testBundle->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue(realpath(__DIR__ . '/../../../Unit/Fixture/ClassWoNamespace')));
        $this->setExpectedException('RuntimeException');
        $this->reader->getResources();
    }
}
