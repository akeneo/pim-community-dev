<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Extension;

use Oro\Bundle\FormBundle\Form\Extension\JsValidationExtension;
use Symfony\Component\Form\FormView;

class JsValidationExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataFactory;

    /**
     * @var JsValidationExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->metadataFactory = $this->getMock('Symfony\Component\Validator\MetadataFactoryInterface');
        $this->extension = new JsValidationExtension($this->metadataFactory);
    }

    public function testBuildView()
    {
        $options = array();

        $view = new FormView();

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->extension->buildView($view, $form, $options);
    }
}
