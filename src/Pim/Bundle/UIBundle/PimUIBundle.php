<?php

namespace Pim\Bundle\UIBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Override OroUIBundle
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimUIBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'OroUIBundle';
    }
}
