<?php

namespace Oro\Bundle\PimDataGridBundle\Extension\MassAction\Actions\Export;

/**
 * Interface for export mass action
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ExportMassActionInterface
{
    /**
     * Get export context for serializer
     *
     * @return array
     */
    public function getExportContext();
}
