<?php

namespace Oro\Bundle\SearchBundle\Tests\Unit\Twig;

use Oro\Bundle\SearchBundle\Twig\OroSearchExtension;

class OroSearchExtensionTest extends \PHPUnit_Framework_TestCase
{
    private $extension;

    public function setUp()
    {
        $twigService = $this->getMock('Twig_Environment');
        $this->extension = new OroSearchExtension($twigService, 'testLayout.html.twig');
    }

    public function testHighlight()
    {
        $result = $this->extension->highlight('test search string', 'search');
        $this->assertEquals(5, strpos($result, '<strong>search</strong>'));
    }

    public function testTrimByString()
    {
        $result = $this->extension->trimByString(
            'Writing Tests for PHPUnit search string The tests',
            'search string',
            15
        );
        $this->assertTrue($result == '...Writing Tests search string...');
    }

    public function testHighlightTrim()
    {
        $result = $this->extension->highlightTrim('Writing Tests for PHPUnit search string The tests', 'search', 15);
        $this->assertTrue($result == '...Writing Tests <strong>search</strong> string...');
    }

    public function testGetName()
    {
        $this->assertEquals('search_extension', $this->extension->getName());
    }

    public function testGetFilters()
    {
        $filters = $this->extension->getFilters();
        $this->assertEquals(3, count($filters));

    }
}
