<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Common\Fake;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EventSubscriberStub implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [];
    }
}
