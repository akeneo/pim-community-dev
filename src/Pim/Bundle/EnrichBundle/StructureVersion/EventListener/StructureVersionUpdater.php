<?php

namespace Pim\Bundle\EnrichBundle\StructureVersion\EventListener;

use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Types\DateTimeType;
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
            StorageEvents::POST_SAVE => 'onPostDBCreate'
        ];
    }

    /**
     * Add the csv format
     */
    public function onPostDBCreate(GenericEvent $event)
    {
        $sql = <<<'SQL'
REPLACE INTO akeneo_structure_version_last_update SET resource_name = :resource_name, last_update = :last_update;
SQL;

        $connection = $this->doctrine->getConnection();
        $now = $connection->convertToDatabaseValue(new \DateTime(), 'datetime');
        $connection->executeUpdate(
            $sql,
            ['resource_name' => ClassUtils::getClass($event->getSubject()), 'last_update' => $now]
        );
    }
}
