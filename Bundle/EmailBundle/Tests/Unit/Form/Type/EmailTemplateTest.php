<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Form\Type;

use Oro\Bundle\EmailBundle\Form\Type\EmailTemplateType;

class EmailTemplateTestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EmailTemplateType
     */
    protected $type;

    public function setUp()
    {
        $this->type = new EmailTemplateType(array());
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
        $this->assertEquals('oro_email_emailtemplate', $this->type->getName());
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->exactly(5))
            ->method('add');

        $this->type->buildForm($builder, array());
    }
}
