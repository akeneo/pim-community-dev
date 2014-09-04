<?php

namespace Pim\Bundle\EnrichBundle\Renderer\PdfBuilder;

use Slik\DompdfBundle\Wrapper\DompdfWrapper;

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
     * @var DompdfWrapper
     */
    protected $dompdfWrapper;

    /**
     * @param DompdfWrapper $dompdfWrapper
     */
    public function __construct(DompdfWrapper $dompdfWrapper)
    {
        $this->dompdfWrapper = $dompdfWrapper;
    }

    /**
     * {@inheritdoc}
     */
    public function buildPdfOutput($htmlInput)
    {
        $this->dompdfWrapper->getpdf($htmlInput);

        return $this->dompdfWrapper->output();
    }
} 
