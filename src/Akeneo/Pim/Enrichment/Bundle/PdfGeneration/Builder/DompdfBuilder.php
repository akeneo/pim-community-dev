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
    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * {@inheritdoc}
     */
    public function buildPdfOutput(string $htmlInput): string
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
    protected function render(string $html): void
    {
        $this->dompdf = new Dompdf([
            'isRemoteEnabled' => true,
        ]);
        $this->dompdf->loadHtml($html);
        $this->dompdf->render();
    }

    /**
     * Get the raw pdf output
     */
    protected function output(): ?string
    {
        return $this->dompdf->output();
    }
}
