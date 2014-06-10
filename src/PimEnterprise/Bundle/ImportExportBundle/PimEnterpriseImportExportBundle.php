<?php

namespace PimEnterprise\Bundle\ImportExportBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Enterprise import export bundle overriden
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PimEnterpriseImportExportBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'PimImportExportBundle';
    }
}
