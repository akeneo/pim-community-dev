<?php

namespace Pim\Bundle\JsFormValidationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimJsFormValidationBundle extends Bundle
{
    public function getParent()
    {
        return 'OroJsFormValidationBundle';
    }
}
