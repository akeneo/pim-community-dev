<?php

namespace Pim\Bundle\DataAuditBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Override data audit bundle
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimDataAuditBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'OroDataAuditBundle';
    }
}
