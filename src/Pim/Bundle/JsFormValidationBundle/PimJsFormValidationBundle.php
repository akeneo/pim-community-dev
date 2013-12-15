<?php

namespace Pim\Bundle\JsFormValidationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Overriden JsFormValidationBundle
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimJsFormValidationBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'APYJsFormValidationBundle';
    }
}
