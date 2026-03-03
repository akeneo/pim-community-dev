<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Infrastructure\Framework\Symfony\Installer;

use Akeneo\Platform\CommunicationChannel\Infrastructure\Framework\Symfony\Installer\Query\CreateViewedAnnouncementsTableQuery;
use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvents;
use Doctrine\DBAL\Connection as DbalConnection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstallerSubscriber implements EventSubscriberInterface
{
    private $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_DB_CREATE => ['createCommunicationChannelTable'],
        ];
    }

    public function createCommunicationChannelTable(): void
    {
        $this->dbalConnection->exec(CreateViewedAnnouncementsTableQuery::QUERY);
    }
}
