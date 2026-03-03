<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\PdfGeneration\HtmlFormatter;

use PhpSpec\ObjectBehavior;

class ArabicHtmlFormatterSpec extends ObjectBehavior
{
    function it_properly_formats_arabic_texts()
    {
        $html = "<div class='test'><span>This text is not changed</span><span>جتنملا مقر</span></div>";

        $this->formatHtml($html)->shouldReturn("<div class='test'><span>This text is not changed</span><span>ﺮﻘﻣ ﻼﻤﻨﺘﺟ</span></div>");
    }

    function it_properly_formats_multiple_arabic_texts()
    {
        $html = "<div class='test'><span>الآن لحضور المؤتمر الدولي العاشر ليونيكود</span><span>جتنملا مقر</span></div>";

        $this->formatHtml($html)->shouldReturn("<div class='test'><span>دﻮﻜﻴﻧﻮﻴﻟ ﺮﺷﺎﻌﻟا ﻲﻟوﺪﻟا ﺮﻤﺗﺆﻤﻟا رﻮﻀﺤﻟ نﻵا</span><span>ﺮﻘﻣ ﻼﻤﻨﺘﺟ</span></div>");
    }

    function it_does_not_alter_other_texts_and_numbers()
    {
        $html = "<div class='test'><span>This text is not changed</span><span>Identifier (1002040549854)</span></div>";

        $this->formatHtml($html)->shouldReturn($html);
    }
}
