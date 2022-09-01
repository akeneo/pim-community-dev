<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Category\OnSave;

use Akeneo\Category\Infrastructure\Component\Model\CategoryTranslation;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetUpdatedPropertyOnTranslationUpdateSubscriber implements EventSubscriber
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::preUpdate
        ];
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        /** @var CategoryTranslation */
        $entity = $args->getObject();
        if (false === $entity instanceof CategoryTranslation) {
            return;
        }

        $entity->getForeignKey()->setUpdated(new \DateTime('now', new \DateTimeZone('UTC')));
    }
}
