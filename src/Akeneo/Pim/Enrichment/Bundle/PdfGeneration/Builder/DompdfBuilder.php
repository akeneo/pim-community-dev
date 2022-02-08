<?php

namespace Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Builder;

use ArPHP\I18N\Arabic;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * PDF builder using DOMPDF library
 *
 * @author    Charles Pourcel <charles.pourcel@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DompdfBuilder implements PdfBuilderInterface
{
    /**
     * @var string
     */
    protected $rootDir;

    /**
     * @var Dompdf
     */
    protected $dompdf;

    private string $publicDir;

    public function __construct(string $rootDir, $publicDir)
    {
        $this->rootDir = $rootDir;
        $this->publicDir = $publicDir;
    }

    /**
     * {@inheritdoc}
     */
    public function buildPdfOutput($htmlInput)
    {
        $this->render($htmlInput);

        return $this->output();
    }

    /**
     * Render a pdf document
     *
     * @param string $html The html to be rendered
     *
     * @throws \LogicException
     */
    protected function render($html)
    {
        $options = new Options([
            'fontDir' => $this->rootDir . '/Akeneo/Pim/Enrichment/Bundle/Resources/fonts',
            'isRemoteEnabled' => true,
            'chroot' => $this->publicDir
        ]);
        $this->dompdf = new Dompdf($options);

        $html = $this->formatArabic($html);
        $this->dompdf->loadHtml($html);
        $this->dompdf->render();
    }

    /**
     * Get the raw pdf output
     *
     * @return string
     */
    protected function output()
    {
        return $this->dompdf->output();
    }

    /**
     * Fixes RTL in Arabic texts
     * see https://github.com/dompdf/dompdf/issues/712#issuecomment-952923539
     */
    private function formatArabic(string $html): string
    {
        $arabic = new Arabic();
        $p = $arabic->arIdentify($html);

        for ($i = count($p)-1; $i >= 0; $i-=2) {
            $utf8ar = $arabic->utf8Glyphs(substr($html, $p[$i-1], $p[$i] - $p[$i-1]), 50, false);
            $html   = substr_replace($html, $utf8ar, $p[$i-1], $p[$i] - $p[$i-1]);
        }

        return $html;
    }
}
