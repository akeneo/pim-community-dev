<?php

namespace PimEnterprise\Bundle\InstallerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Installer bundle
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PimEnterpriseInstallerBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'PimInstallerBundle';
    }
}
