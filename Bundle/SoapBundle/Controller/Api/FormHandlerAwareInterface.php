<?php

namespace Oro\Bundle\SoapBundle\Controller\Api;

use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;

interface FormHandlerAwareInterface
{
    /**
     * @return ApiFormHandler
     */
    public function getFormHandler();
}
