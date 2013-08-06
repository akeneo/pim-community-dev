<?php
namespace Oro\Bundle\UIBundle\Tests\Unit\Twig;

use Oro\Bundle\UIBundle\Tests\Unit\Twig\Template\TestJson;
use Oro\Bundle\UIBundle\Tests\Unit\Twig\Template\TestString;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    public function testStringRender()
    {
        $object = new TestString(new \Twig_Environment());
        $output = $object->render(array());
        $this->assertContains('<!-- Start Template: string.twig -->', $output);
    }

    public function testJsonRender()
    {
        $object = new TestJson(new \Twig_Environment());
        $output = $object->render(array());
        $output = json_decode($output);
        $this->assertEquals('json.twig', $output->template_name);
        $this->assertContains('<!-- Start Template: json.twig -->', $output->content);
        $this->assertContains('test', $output->content);
    }
}
