<?php

namespace Akeneo\Pim\Enrichment\Bundle\PdfGeneration\HtmlFormatter;

use ArPHP\I18N\Arabic;

/**
 * PDF builder using DOMPDF library
 *
 * @author    Charles Pourcel <charles.pourcel@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ArabicHtmlFormatter implements HtmlFormatter
{
    public function formatHtml(string $html): string
    {
        $arabic = new Arabic();
        $arabicIndexes = $arabic->arIdentify($html);

        for ($i = count($arabicIndexes)-1; $i >= 0; $i-=2) {
            $utf8ar = $arabic->utf8Glyphs(substr($html, $arabicIndexes[$i-1], $arabicIndexes[$i] - $arabicIndexes[$i-1]), 50, false);
            $html   = substr_replace($html, $utf8ar, $arabicIndexes[$i-1], $arabicIndexes[$i] - $arabicIndexes[$i-1]);
        }

        return $html;
    }
}
