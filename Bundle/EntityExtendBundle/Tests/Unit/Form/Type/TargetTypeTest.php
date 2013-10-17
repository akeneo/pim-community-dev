<?php

namespace Oro\Bundle\EntityExtendBundle\Tests\Unit\Form\Type;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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

    protected function setUp()
    {
        $this->configManager = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getEntityManager', 'getIds'))
            ->getMock();

        $this->request = new Request($request = array('id' => 1));

        $this->type = new TargetType($this->configManager, $this->request);

        $this->resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
    }

    public function testNames()
    {
        $this->assertEquals('oro_entity_target_type', $this->type->getName());
        $this->assertEquals('choice', $this->type->getParent());
    }

    public function testOptionsEdit()
    {
        $entity = $this->getMock('Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel');
        $entity
            ->expects($this->once())
            ->method('getClassName')
            ->will($this->returnValue('Oro\Bundle\UserBundle\Entity\User'));

        $field = $this->getMock('Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel');
        $field
            ->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($entity));

        $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo
            ->expects($this->once())
            ->method('find')
            ->will($this->returnValue($field));

        $em = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\OroEntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em
            ->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repo));

        $this->configManager
            ->expects($this->once())
            ->method('getEntityManager')
            ->will($this->returnValue($em));

        $this->configManager
            ->expects($this->once())
            ->method('getIds')
            ->will($this->returnValue(array(new EntityConfigId('Oro\Bundle\UserBundle\Entity\User', 'entity'))));

        $this->type->setDefaultOptions($this->resolver);
    }

    public function testOptionsCreate()
    {
        $this->request = new Request(
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
