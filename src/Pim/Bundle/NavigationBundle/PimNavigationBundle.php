<?php
namespace Pim\Bundle\NavigationBundle;

use Oro\Bundle\NavigationBundle\OroNavigationBundle;

/**
 * Override oro navigation
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimNavigationBundle extends OroNavigationBundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'OroNavigationBundle';
    }
}
