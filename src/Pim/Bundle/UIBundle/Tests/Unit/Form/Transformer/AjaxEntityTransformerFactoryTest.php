<?php

namespace Pim\Bundle\UIBundle\Tests\Unit\Form\Transformer;

use Pim\Bundle\UIBundle\Form\Transformer\AjaxEntityTransformerFactory;

/**
 * Tests related class
 * 
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AjaxEntityTransformerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $doctrine = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $transformerClass = 'Pim\Bundle\UIBundle\Form\Transformer\AjaxEntityTransformer';
        $options = array(
            'class' => 'class'
        );
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('class'))
            ->will(
                $this->returnValue($this->getMock('Pim\Bundle\UIBundle\Entity\Repository\OptionRepositoryInterface'))
            );
        
        $factory = new AjaxEntityTransformerFactory($doctrine, $transformerClass);
        $result = $factory->create($options);
        $this->assertInstanceOf($transformerClass, $result);
    }
}
