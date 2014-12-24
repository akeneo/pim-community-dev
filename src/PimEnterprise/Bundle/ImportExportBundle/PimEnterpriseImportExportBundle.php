<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ImportExportBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Enterprise import export bundle overriden
 *
 * @author Romain Monceau <romain@akeneo.com>
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
