<?php

namespace Oro\Bundle\SoapBundle\Tests\Unit\Form\Extension;

use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\SoapBundle\Form\Extension\DateFormExtension;
use Oro\Bundle\FormBundle\Form\Type\OroDateType;

class DateFormExtensionTest extends \PHPUnit_Framework_TestCase
{
    const API_URL_PREFIX = '/api';

    public function testGetExtendedType()
    {
        $extension = $this->createDateExtension();
        $this->assertEquals(OroDateType::NAME, $extension->getExtendedType());
    }

    /**
     * @param string $path
     * @param bool|null $expectedLocalization
     * @dataProvider setDefaultOptionsDataProvider
     */
    public function testSetDefaultOptions($path, $expectedLocalization = null)
    {
        $extension = $this->createDateExtension($path);
        $optionsResolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolverInterface')
            ->disableOriginalConstructor()
            ->setMethods('setDefaults')
            ->getMockForAbstractClass();

        if ($expectedLocalization !== null) {
            $optionsResolver->expects($this->once())
                ->method('setDefaults')
                ->with(array('localized_format' => $expectedLocalization));
        } else {
            $optionsResolver->expects($this->never())
                ->method('setDefaults');
        }

        $extension->setDefaultOptions($optionsResolver);
    }

    /**
     * @return array
     */
    public function setDefaultOptionsDataProvider()
    {
        return array(
            'not api' => array(
                'path' => '/user/update',
            ),
            'api' => array(
                'path' => '/api/rest/user',
                'expectedLocalization' => false,
            ),
        );
    }

    /**
     * @param string $path
     * @return DateFormExtension
     */
    protected function createDateExtension($path = '')
    {
        $request = $this->getRequest($path);
        return new DateFormExtension($request, self::API_URL_PREFIX);
    }

    /**
     * @param string $pathInfo
     * @return Request
     */
    protected function getRequest($pathInfo)
    {
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->setMethods(array('getPathInfo'))
            ->getMock();
        $request->expects($this->any())
            ->method('getPathInfo')
            ->will($this->returnValue($pathInfo));

        return $request;
    }
}
