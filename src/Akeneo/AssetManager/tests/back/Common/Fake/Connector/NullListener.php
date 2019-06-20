<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Common\Fake\Connector;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

/**
 * Can be used in unit testing, in a situations where a firewall listener is not needed.
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class NullListener implements ListenerInterface
{
    public function handle(GetResponseEvent $event): void
    {
    }
}
