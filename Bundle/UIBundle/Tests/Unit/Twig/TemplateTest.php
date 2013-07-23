<?php
namespace Oro\Bundle\UIBundle\Twig;

class StringTemplateTest extends Template
{
    public function getTemplateName()
    {
        return 'string.twig';
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        echo 'test string';
    }
}

class JsonTemplateTest extends Template
{
    public function getTemplateName()
    {
        return 'json.twig';
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        echo json_encode(array('content' => 'test'));
    }
}


class TemplateTest extends \PHPUnit_Framework_TestCase
{
    public function testStringRender()
    {
        $object = new StringTemplateTest(new \Twig_Environment());
        $output = $object->render(array());
        $this->assertContains('<!-- Start Template: string.twig -->', $output);
    }

    public function testJsonRender()
    {
        $object = new JsonTemplateTest(new \Twig_Environment());
        $output = $object->render(array());
        $output = json_decode($output);
        $this->assertTrue($output->template_name == 'json.twig');
        $this->assertContains('<!-- Start Template: json.twig -->', $output->content);
    }
}
