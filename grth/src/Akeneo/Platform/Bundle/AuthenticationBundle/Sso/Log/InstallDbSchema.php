<?php


namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InstallDbSchema implements EventSubscriberInterface
{
    private $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_DB_CREATE => 'install'
        ];
    }

    public function install(InstallerEvent $event): void
    {
        $this->dbalConnection->executeQuery(
            <<<SQL
CREATE TABLE pimee_sso_log (
  time DATETIME,
  channel VARCHAR(255),
  level SMALLINT,
  message TEXT,
  INDEX(time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
        );
    }
}
