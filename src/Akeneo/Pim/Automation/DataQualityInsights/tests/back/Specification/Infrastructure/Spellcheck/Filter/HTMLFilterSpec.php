<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck\Filter;

use PhpSpec\ObjectBehavior;

/**
 * @license   https://opensource.org/licenses/MIT MIT
 * @source    https://github.com/mekras/php-speller
 */
class HTMLFilterSpec extends ObjectBehavior
{
    /**
     * Test basics.
     */
    public function it_tests_basics(): void
    {
        $html = "<br>foo&reg; <a\nhref = '#' title='bar'>\nbaz</a>";
        $this->beConstructedWith($html);

        $text = "    foo        \n                  bar  \nbaz    ";
        $this->filter($html)->shouldEqual($text);
    }

    /**
     * Only for "keywords" and "description" meta tags "content" attr should be treated as string.
     */
    public function it_tests_meta_content(): void
    {
        $html =
            '<META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html" />' . "\n" .
            '<meta name="Keywords" content="Foo">' . "\n" .
            '<meta name="foo" content="Foobar">' . "\n" .
            '<meta name="description" content="Bar">';
        $text =
            "                                                      \n" .
            "                               Foo  \n" .
            "                                  \n" .
            '                                  Bar  ';
        $this->beConstructedWith($html);
        $this->filter($html)->shouldEqual($text);
    }

    /**
     * <script> content should be filtered out.
     */
    public function it_tests_script(): void
    {
        $html = "<p>Foo</p>\n<script type=\"text/javascript\">Bar Baz\nBuz</script>";
        $text = "   Foo    \n                                      \n            ";
        $this->beConstructedWith($html);
        $this->filter($html)->shouldEqual($text);
    }

    public function it_tests_malformed_attribute(): void
    {
        $html = '<p ""="">test</p>';
        $text = '         test    ';
        $this->beConstructedWith($html);
        $this->filter($html)->shouldEqual($text);
    }

    public function it_tests_malformed_attribute_2(): void
    {
        $html = '<p ">test</p>';
        $text = '     test    ';
        $this->beConstructedWith($html);
        $this->filter($html)->shouldEqual($text);
    }

    public function it_tests_malformed_attribute_3(): void
    {
        $html = '<p name=">test</p>';
        $text = '          test    ';
        $this->beConstructedWith($html);
        $this->filter($html)->shouldEqual($text);
    }

    public function it_tests_malformed_attribute_4(): void
    {
        $html = '<p name"=">test</p>';
        $text = '           test    ';
        $this->beConstructedWith($html);
        $this->filter($html)->shouldEqual($text);
    }

    public function it_tests_malformed_attribute_5(): void
    {
        $html = '<p "name=">test</p>';
        $text = '           test    ';
        $this->beConstructedWith($html);
        $this->filter($html)->shouldEqual($text);
    }

    public function testMalformedTags(): void
    {
        $html = "foo/>bar<br><br/>";
        $text = "foo/ bar         ";
        $this->beConstructedWith($html);
        $this->filter($html)->shouldEqual($text);
    }
}
