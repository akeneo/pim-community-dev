<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Form\Type;

use Oro\Bundle\EmailBundle\Form\Type\EmailTemplateTranslationType;

class EmailTemplateTranslationTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EmailTemplateTranslationType
     */
    protected $type;

    public function setUp()
    {
        $this->type = new EmailTemplateTranslationType(array());
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
        $this->assertEquals('oro_email_emailtemplate_translatation', $this->type->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals('a2lix_translations_gedmo', $this->type->getParent());
    }
}
