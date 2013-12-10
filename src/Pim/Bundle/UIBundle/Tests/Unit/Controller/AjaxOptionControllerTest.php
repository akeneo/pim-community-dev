<?php

namespace Pim\Bundle\UIBundle\Tests\Unit\Controller;

use Pim\Bundle\UIBundle\Controller\AjaxOptionController;

/**
 * Tests related class
 * 
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AjaxOptionControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testListAction()
    {
        $doctrine = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $repository = $this->getMock('Pim\Bundle\UIBundle\Entity\Repository\OptionRepositoryInterface');
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('class'))
            ->will($this->returnValue($repository));
        $repository->expects($this->once())
            ->method('getOptions')
            ->with(
                $this->equalTo('data_locale'),
                $this->equalTo('collection_id'),
                $this->equalTo('search'),
                $this->equalTo(array('options'))
            )
            ->will($this->returnValue(array('success' => true)));
                
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->query = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $arguments = array(
            'class'         => 'class',
            'dataLocale'    => 'data_locale',
            'collectionId'  => 'collection_id',
            'search'        => 'search',
            'options'       => array('options')
        );
        $request->query->expects($this->any())
            ->method('get')
            ->will(
                $this->returnCallback(
                    function ($key) use ($arguments) {
                        return $arguments[$key];
                    }
                )
            );

        $controller = new AjaxOptionController($doctrine);
        $result = $controller->listAction($request);
        $this->assertEquals('{"success":true}', $result->getContent());
    }
}
