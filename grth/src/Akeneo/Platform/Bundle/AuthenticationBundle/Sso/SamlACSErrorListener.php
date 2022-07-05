<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso;

use OneLogin\Saml2\Error as OneLoginError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * @author Griffins
 */
class SamlACSErrorListener
{
    public function __invoke(ExceptionEvent $event)
    {
        $t = $event->getThrowable();
        if (!($t instanceof OneLoginError)) {
            // here we are only interested in error thrown by onelogin Saml2
            return;
        }

        $response = $event->getResponse() ?? new Response('', Response::HTTP_BAD_REQUEST);

        $event->setResponse($response);
    }
}
