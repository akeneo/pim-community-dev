<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Fakes;

use Akeneo\Tool\Component\Versioning\Model\TimestampableInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TimestampableSubscriber implements EventSubscriber
{
    public function __construct(
        private Clock $clock
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if (!$object instanceof TimestampableInterface) {
            return;
        }

        $object->setCreated(\DateTime::createFromImmutable($this->clock->now()));
        $object->setUpdated(\DateTime::createFromImmutable($this->clock->now()));
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if (!$object instanceof TimestampableInterface) {
            return;
        }

        $object->setUpdated(\DateTime::createFromImmutable($this->clock->now()));
    }
}
