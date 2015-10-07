<?php
namespace Oro\Bundle\UIBundle\Tests\Unit\Twig;

use Oro\Bundle\UIBundle\Tests\Unit\Twig\Template\TestJSON;
use Oro\Bundle\UIBundle\Tests\Unit\Twig\Template\TestHTML;
use Oro\Bundle\UIBundle\Tests\Unit\Twig\Template\TestJS;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    public function testHtmlRender()
    {
        $object = new TestHTML(new \Twig_Environment());
        $output = $object->render(array());
        $this->assertContains('<!-- Start Template: block.html.twig -->', $output);
    }

    public function testJsonRender()
    {
        $object = new TestJSON(new \Twig_Environment());
        $output = $object->render(array());
        $output = json_decode($output);
        $this->assertEquals('data.json.twig', $output->template_name);
        $this->assertContains('<!-- Start Template: data.json.twig -->', $output->content);
        $this->assertContains('<p>test</p>', $output->content);
    }

    public function testJsRender()
    {
        $object = new TestJS(new \Twig_Environment());
        $output = $object->render(array());
        $this->assertNotContains('<!-- Start Template:', $output);
        $this->assertContains('"test"', $output);
    }
}
