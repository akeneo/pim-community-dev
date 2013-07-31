<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Form\Type;

use Oro\Bundle\EmailBundle\Form\Type\EmailTemplateApiType;

class EmailTemplateApiTestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EmailTemplateApiType
     */
    protected $type;

    public function setUp()
    {
        $this->type = new EmailTemplateApiType(array());
    }

    public function tearDown()
    {
        unset($this->type);
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));

        $this->type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals('oro_email_emailtemplate_api', $this->type->getName());
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->once())
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf('Oro\Bundle\UserBundle\Form\EventListener\PatchSubscriber'));

        $this->type->buildForm($builder, array());
    }
}
