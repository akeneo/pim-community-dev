<?php

namespace Oro\Bundle\SoapBundle\Controller\Api;

use Symfony\Component\Form\FormInterface;

interface FormAwareInterface
{
    /**
     * @return FormInterface
     */
    public function getForm();
}
