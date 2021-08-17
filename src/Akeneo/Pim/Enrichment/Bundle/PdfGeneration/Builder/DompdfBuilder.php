<?php

namespace Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Builder;

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
    protected string $rootDir;
    protected ?Dompdf $dompdf;

    private string $publicDir;

    public function __construct(string $rootDir, string $publicDir)
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
}
