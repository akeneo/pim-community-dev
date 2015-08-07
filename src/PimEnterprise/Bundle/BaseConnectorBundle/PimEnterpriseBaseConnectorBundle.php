<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\BaseConnectorBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Enterprise BaseConnectorBundle
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class PimEnterpriseBaseConnectorBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'PimBaseConnectorBundle';
    }
}
