<?php

namespace Pim\Bundle\EntityBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Override oro entity bundle
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimEntityBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'OroEntityBundle';
    }
}
