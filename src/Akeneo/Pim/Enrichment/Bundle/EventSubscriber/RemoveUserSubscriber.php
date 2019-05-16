<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @todo merge 3.2: Remove this class and the associated service definition
 *                  We should instead add an 'ON DELETE SET NULL' clause to the author foreign key constraint
 *
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveUserSubscriber implements EventSubscriberInterface
{
    /** @var Connection */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_REMOVE => 'removeAuthorFromComments',
            StorageEvents::POST_REMOVE => 'commitAuthorRemoval',
        ];
    }

    public function removeAuthorFromComments(RemoveEvent $event): void
    {
        $user = $event->getSubject();
        if (!$user instanceof UserInterface) {
            return;
        }

        $this->connection->beginTransaction();
        $this->connection->executeQuery(
            'UPDATE pim_comment_comment SET author_id = NULL WHERE author_id = :userId',
            [
                'userId' => $user->getId(),
            ]
        );
    }

    public function commitAuthorRemoval(RemoveEvent $event): void
    {
        if (!$event->getSubject() instanceof UserInterface) {
            return;
        }

        if ($this->connection->isTransactionActive()) {
            $this->connection->commit();
        }
    }
}
