<?php

namespace Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Builder;

use Dompdf\Dompdf;

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

    /**
     * @param string $rootDir
     */
    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
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
        $this->dompdf = new Dompdf([
            'isRemoteEnabled' => true,
        ]);
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
