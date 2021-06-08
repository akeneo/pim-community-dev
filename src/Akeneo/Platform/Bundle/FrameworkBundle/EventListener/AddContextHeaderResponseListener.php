<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\FrameworkBundle\EventListener;

use Akeneo\Platform\Bundle\FrameworkBundle\BoundedContext\BoundedContextResolver;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Add a custom context header in the response to facilitate log analysis
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class AddContextHeaderResponseListener
{
    private const HEADER_AKENEO_CONTEXT = 'x-akeneo-context';

    private BoundedContextResolver $boundedContextResolver;

    public function __construct(BoundedContextResolver $boundedContextResolver)
    {
        $this->boundedContextResolver = $boundedContextResolver;
    }

    public function injectAkeneoContextHeader(ResponseEvent $event): void
    {
        $event->getResponse()->headers->set(
            self::HEADER_AKENEO_CONTEXT,
            $this->boundedContextResolver->fromRequest($event->getRequest())
        );
    }
}
