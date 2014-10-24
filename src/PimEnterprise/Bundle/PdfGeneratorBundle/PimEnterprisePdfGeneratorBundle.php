<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
