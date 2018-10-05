<?php

namespace Akeneo\Platform\Bundle\UIBundle\Tests\Unit\Form\Transformer;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeOptionRepository;
use Akeneo\Platform\Bundle\UIBundle\Form\Transformer\AjaxEntityTransformer;
use Akeneo\Platform\Bundle\UIBundle\Form\Transformer\AjaxEntityTransformerFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AjaxEntityTransformerFactoryTest extends TestCase
{
    public function testCreate()
    {
        $doctrine = $this->createMock(RegistryInterface::class);
        $transformerClass = AjaxEntityTransformer::class;
        $options = [
            'class' => 'class'
        ];
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('class'))
            ->will(
                $this->returnValue($this->createMock(
                    AttributeOptionRepository::class
                ))
            );

        $factory = new AjaxEntityTransformerFactory($doctrine, $transformerClass);
        $result = $factory->create($options);
        $this->assertInstanceOf($transformerClass, $result);
    }
}
