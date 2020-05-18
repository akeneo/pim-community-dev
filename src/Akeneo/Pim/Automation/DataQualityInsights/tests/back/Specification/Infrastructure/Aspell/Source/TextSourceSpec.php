<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell\Source;

use PhpSpec\ObjectBehavior;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class TextSourceSpec extends ObjectBehavior
{
    public function it_returns_string_content_as_plain_text()
    {
        $text = 'lorem ipsum';
        $this->beConstructedWith($text);

        $this->getAsString()->shouldBe($text);
    }

    public function it_returns_multilines_string_content_as_plain_text()
    {
        $text = "lorem\nipsum";
        $this->beConstructedWith($text);

        $this->getAsString()->shouldBe($text);
    }

    public function it_returns_html_content_as_plain_text()
    {
        $html = '<p><br/></p>';
        $expectedText = "            ";
        $this->beConstructedWith($html);

        $this->getAsString()->shouldBeEqualTo($expectedText);
    }

    public function it_returns_html_content_with_attribute_as_plain_text()
    {
        $html = '<span style="color:red;">lorem ipsum</span>';
        $expectedText = "                         lorem ipsum       ";
        $this->beConstructedWith($html);

        $this->getAsString()->shouldBeEqualTo($expectedText);
    }

    public function it_returns_encoding()
    {
        $locale = 'UTF-8';

        $this->beConstructedWith('lorem ipsum', $locale);

        $this->getEncoding()->shouldBe($locale);
    }
}
