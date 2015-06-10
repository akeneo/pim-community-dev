<?php

namespace Pim\Bundle\UserBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Firewall\ExceptionListener as BaseExceptionListener;

class FirewallExceptionListener extends BaseExceptionListener
{
    protected function setTargetPath(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            return;
        }

        parent::setTargetPath($request);
    }
}
