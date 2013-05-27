<?php

namespace Oro\Bundle\JsFormValidationBundle\Tests\Unit\Generator;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Validator\Mapping\GetterMetadata;

use Oro\Bundle\JsFormValidationBundle\Generator\GettersLibraries;

class GettersLibrariesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $container;

    /**
     * @var GettersLibraries
     */
    protected $model;

    protected function setUp()
    {
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $formView = $this->getMock('Symfony\Component\Form\FormView');
        $this->model = new GettersLibraries($this->container, $formView);
    }

    /**
     * @dataProvider getBundleDataProvider
     */
    public function testGetBundle($className, $allBundles, $expectedBundleName)
    {
        $getterMetadata = $this->createMockGetterMetadata($className);

        $this->container->expects($this->once())->method('getParameter')
            ->with('kernel.bundles')->will($this->returnValue($allBundles));
        $this->assertEquals($expectedBundleName, $this->model->getBundle($getterMetadata));
    }


    /**
     * @param string $className
     * @return GetterMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createMockGetterMetadata($className)
    {
        $result = $this->getMockBuilder('Symfony\Component\Validator\Mapping\GetterMetadata')
            ->setMethods(array('getClassName'))
            ->disableOriginalConstructor()
            ->getMock();
        $result->expects($this->any())->method('getClassName')->will($this->returnValue($className));
        return $result;
    }

    /**
     * @return array
     */
    public function getBundleDataProvider()
    {
        return array(
            array(
                'Foo\BarBundle\Entity\Baz',
                array(
                    'FrameworkBundle' => 'Symfony\Bundle\FrameworkBundle\FrameworkBundle',
                    'FooBarBundle' => 'Foo\BarBundle\FooBarBundle'
                ),
                'FooBarBundle'
            ),
            array(
                'Foo\BarBundle\Entity\Baz',
                array(
                    'FrameworkBundle' => 'Symfony\Bundle\FrameworkBundle\FrameworkBundle'
                ),
                'FooBarBundle'
            ),
            array(
                'Foo\BarBundle\Entity\Baz',
                array(
                    'FrameworkBundle' => 'Symfony\Bundle\FrameworkBundle\FrameworkBundle',
                    'BarBundle' => 'Foo\BarBundle\BarBundle'
                ),
                'BarBundle'
            ),
            array(
                'Foo\Bundle\BarBundle\Entity\Baz',
                array(
                    'FrameworkBundle' => 'Symfony\Bundle\FrameworkBundle\FrameworkBundle',
                    'FooBarBundle' => 'Foo\Bundle\BarBundle\FooBarBundle'
                ),
                'FooBarBundle'
            ),
            array(
                'Foo',
                array(),
                null
            ),
            array(
                'Foo\Bar',
                array(),
                null
            )
        );
    }
}
