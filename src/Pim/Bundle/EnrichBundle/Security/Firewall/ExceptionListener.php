<?php

namespace Pim\Bundle\EnrichBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Firewall\ExceptionListener as BaseExceptionListener;

/**
 * Redirects to hash url after login
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExceptionListener extends BaseExceptionListener
{
    /**
     * {@inheritdoc}
     */
    protected function setTargetPath(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            return;
        }

        if ($request->hasSession() && $request->isMethodSafe()) {
            if (null !== $qs = $request->getQueryString()) {
                $qs = '?' . $qs;
            }
            $targetPath = $request->getBaseUrl() . $request->getPathInfo() . $qs;

            $request->getSession()->set('__target_path', $targetPath);
        }
    }
}
