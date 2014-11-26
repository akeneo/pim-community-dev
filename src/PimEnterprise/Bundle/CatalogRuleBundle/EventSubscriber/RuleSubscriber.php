<?php
/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle\Event;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Rule Subscriber
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RuleSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            'postRemove',
            'preRemove',
        ];
    }

    public function postRemove(LifecycleEventArgs $args)
    {

    }

    public function preRemove(LifecycleEventArgs $args)
    {

    }
}
