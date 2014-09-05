<?php

namespace PimEnterprise\Bundle\PdfGeneratorBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * PDF Generator bundle
 *
 * @author Charles Pourcel <charles.pourcel@akeneo.com>
 */
class PimEnterprisePdfGeneratorBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'PimPdfGeneratorBundle';
    }
}
