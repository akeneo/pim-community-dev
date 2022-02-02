<?php

namespace Akeneo\UserManagement\Bundle\EventListener;

use Akeneo\UserManagement\Component\Model\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

/**
 * User event
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/** TODO Pull up to 6.0 Remove this subscriber */
class LoadLoginAttemptInformationSubscriber implements EventSubscriber
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postLoad
        ];
    }

    public function postLoad(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getObject();

        if (!$entity instanceof User) {
            return;
        }

        $this->updateSchemaIfColumnNotExist();
        $this->hydrateLoginAttemptInformation($entity);
    }

    public function updateSchemaIfColumnNotExist(): void
    {
        $columns = $this->connection->getSchemaManager()->listTableColumns("oro_user");
        if (!isset($columns["consecutive_authentication_failure_counter"], $columns["authentication_failure_reset_date"])) {
            $this->connection->executeUpdate('ALTER TABLE oro_user ADD consecutive_authentication_failure_counter INT DEFAULT 0');
            $this->connection->executeUpdate('ALTER TABLE oro_user ADD authentication_failure_reset_date datetime  DEFAULT NULL');
        }
    }

    public function hydrateLoginAttemptInformation(User $user): void
    {
        $statement = $this->connection->executeQuery('
            SELECT consecutive_authentication_failure_counter, authentication_failure_reset_date FROM oro_user WHERE id = :id
        ', ["id" => $user->getId()]);
        $values = $statement->fetch();
        $user->setConsecutiveAuthenticationFailureCounter($values["consecutive_authentication_failure_counter"]);
        $user->setAuthenticationFailureResetDate(Type::getType(Types::DATETIME_MUTABLE)->convertToPHPValue($values["authentication_failure_reset_date"], $this->connection->getDatabasePlatform()));
    }
}
