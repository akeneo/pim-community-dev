<?php

namespace Oro\Bundle\JsFormValidationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroJsFormValidationBundle extends Bundle
{
    public function getParent()
    {
        return 'APYJsFormValidationBundle';
    }
}
