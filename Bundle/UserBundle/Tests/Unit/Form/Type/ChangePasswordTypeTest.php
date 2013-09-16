<?php

namespace Oro\Bundle\UserBundle\Tests\Unit\Type;

use Oro\Bundle\UserBundle\Form\Type\ChangePasswordType;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

class ChangePasswordTypeTest extends FormIntegrationTestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $aclManager;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $securityContext;

    /** @var  ChangePasswordType */
    protected $type;

    public function setUp()
    {
        parent::setUp();

        $this->aclManager = $this->getMockBuilder('Oro\Bundle\UserBundle\Acl\Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->securityContext = $this->getMockForAbstractClass(
            'Symfony\Component\Security\Core\SecurityContextInterface'
        );

        $this->type = new ChangePasswordType($this->aclManager, $this->securityContext);
    }

    /**
     * Test buildForm
     */
    public function testBuildForm()
    {
        $builder = $this->getMock('Symfony\Component\Form\Test\FormBuilderInterface');
        $options = array();

        $builder->expects($this->once())
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf('Oro\Bundle\UserBundle\Form\EventListener\ChangePasswordSubscriber'));

        $builder->expects($this->exactly(2))
            ->method('add')
            ->will($this->returnSelf());

        $this->type->buildForm($builder, $options);
    }

    /**
     * Test defaults
     */
    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->type->setDefaultOptions($resolver);
    }

    /**
     * Test name
     */
    public function testName()
    {
        $this->assertEquals('oro_change_password', $this->type->getName());
    }
}
