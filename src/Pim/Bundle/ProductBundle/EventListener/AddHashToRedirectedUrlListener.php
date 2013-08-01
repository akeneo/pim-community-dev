<?php

namespace Pim\Bundle\ProductBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * 
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddHashToRedirectedUrlListener
{
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();

        if ($response instanceof RedirectResponse) {
            $request = $event->getRequest();
            if ($request->query->has('hash')) {
                $response->setTargetUrl(
                    sprintf(
                        '%s#%s',
                        $response->getTargetUrl(),
                        $request->query->get('hash')
                    )
                );
            }
        }
    }
}

