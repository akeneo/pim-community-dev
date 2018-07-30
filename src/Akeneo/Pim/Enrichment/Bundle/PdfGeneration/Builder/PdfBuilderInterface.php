<?php

namespace Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Builder;

/**
 * Interface for PDF builder
 *
 * @author    Charles Pourcel <charles.pourcel@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface PdfBuilderInterface
{
    /**
     * @param string $htmlInput
     *
     * @return string
     */
    public function buildPdfOutput($htmlInput);
}
