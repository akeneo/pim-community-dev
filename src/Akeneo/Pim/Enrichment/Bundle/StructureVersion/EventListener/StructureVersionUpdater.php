<?php

namespace Akeneo\Pim\Enrichment\Bundle\StructureVersion\EventListener;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Listener on the post save event to update the last update date on the structure version table
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StructureVersionUpdater implements EventSubscriberInterface
{
    /** @var RegistryInterface */
    protected $doctrine;

    /**
     * @param RegistryInterface $doctrine
     */
    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_SAVE => 'onPostSave',
            StorageEvents::POST_SAVE_ALL => 'onPostSaveAll',
        ];
    }


    public function onPostSave(GenericEvent $event)
    {
        $subject = $event->getSubject();

        if (!is_object($subject)) {
            return;
        }

        if ($subject instanceof ProductInterface || $subject instanceof ProductModelInterface) {
            return;
        }

        if ($event->hasArgument('unitary') && $event->getArgument('unitary')) {
            $this->replaceVersionLastUpdate(ClassUtils::getClass($event->getSubject()));
        }
    }

    public function onPostSaveAll(GenericEvent $event)
    {
        $subject = current($event->getSubject());

        if (!is_object($subject)) {
            return;
        }

        if ($subject instanceof ProductInterface || $subject instanceof ProductModelInterface) {
            return;
        }

        $this->replaceVersionLastUpdate(ClassUtils::getClass($subject));
    }

    private function replaceVersionLastUpdate($subject): void
    {
        $sql = <<<'SQL'
REPLACE INTO akeneo_structure_version_last_update SET resource_name = :resource_name, last_update = now();
SQL;

        $connection = $this->doctrine->getConnection();
        $connection->executeUpdate($sql, [
            'resource_name' => $subject,
        ]);
    }
}
