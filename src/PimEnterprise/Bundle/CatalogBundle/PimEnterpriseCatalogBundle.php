<?php

namespace PimEnterprise\Bundle\CatalogBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * PIM Enterprise Catalog Bundle
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimEnterpriseCatalogBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'PimCatalogBundle';
    }
}
