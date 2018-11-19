<?php

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Category;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryTranslationInterface;

/**
 * Updated at management
 * If the translation of the category is updated, the category itself is not considered as changed by Doctrine
 *
 * @author    Aurélien Lavorel <aurelien@lavoweb.net>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryTranslationSubscriber implements EventSubscriber
{
    /**
     * Specifies the list of events to listen
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate
        ];
    }

    /**
     * Before update
     *
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $object = $args->getObject();

        if (!$object instanceof CategoryTranslationInterface) {
            return;
        }

        $object->getForeignKey()->setUpdated(new \DateTime('now', new \DateTimeZone('UTC')));
    }
}
