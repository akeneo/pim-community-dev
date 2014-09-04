<?php

namespace Pim\Bundle\EnrichBundle\Renderer\PdfBuilder;

/**
 * PDF builder using DOMPDF wrapper
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
     * @var DOMPDF
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
     * @param  string $html    The html to be rendered
     * @param  string $docname The name of the document to be served
     */
    protected function render($html)
    {
        // DOMPDF doesn't follow PSR convention, it uses classmap in autoload
        define('DOMPDF_ENABLE_AUTOLOAD', false);
        $filePath = $this->rootDir."/../vendor/dompdf/dompdf/dompdf_config.inc.php";
        if (file_exists($filePath)) {
            require_once($filePath);
        } else {
            throw new \LogicException('DomPDF cannot be loaded');
        }

        $this->dompdf = new \DOMPDF();
        $this->dompdf->set_paper(DOMPDF_DEFAULT_PAPER_SIZE);
        $this->dompdf->load_html($html);
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
