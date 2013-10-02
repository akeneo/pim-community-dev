<?php

namespace Oro\Bundle\EntityExtendBundle\Tests\Unit\Form\Type;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\EntityBundle\ORM\OroEntityManager;
use Oro\Bundle\EntityConfigBundle\Config\Config;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use Oro\Bundle\EntityExtendBundle\Form\Type\TargetType;

class TargetTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ConfigManager */
    protected $configManager;

    /** @var  Request */
    protected $request;

    /** @var  TargetType */
    protected $type;

    /** @var OptionsResolverInterface */
    protected $resolver;

    public function setUp()
    {
        $this->configManager = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getEntityManager', 'getIds'))
            ->getMock();

        $this->request = new Request();

        $this->type = new TargetType($this->configManager, $this->request);

        $this->resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
    }

    public function testNames()
    {
        $this->assertEquals('oro_entity_target_type', $this->type->getName());
        $this->assertEquals('choice', $this->type->getParent());
    }

    public function testOptions()
    {
        $repo = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getRepository'))
            ->getMock();

        $repo
            ->expects($this->any())
            ->method('getRepository')
            //->will($this->anything())
        ;

        //$this->type->setDefaultOptions($this->resolver);
    }

    public function testOptionsWithRequest()
    {
        $this->request = new Request(
            $query = array(),
            $request = array(
                'entity' => new \Oro\Bundle\UserBundle\Entity\User(),
                'id'     => 1,
            )
        );

        $this->configManager
            ->expects($this->once())
            ->method('getIds')
            ->will($this->returnValue(array(new EntityConfigId('Oro\Bundle\UserBundle\Entity\User', 'entity'))));

        $this->type = new TargetType($this->configManager, $this->request);

        $this->resolver
            ->expects($this->once())
            ->method('setDefaults');

        $this->type->setDefaultOptions($this->resolver);
    }
}
