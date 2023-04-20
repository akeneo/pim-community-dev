<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Install;

use Akeneo\Connectivity\Connection\Application\Apps\Command\GenerateAsymmetricKeysCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\GenerateAsymmetricKeysHandler;
use Akeneo\Platform\Installer\Domain\Event\InstallerEvents;
use Doctrine\DBAL\Connection as DbalConnection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstallSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DbalConnection $dbalConnection,
        private GenerateAsymmetricKeysHandler $generateAsymmetricKeysHandler,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_DB_CREATE => ['updateSchema', -10],
            InstallerEvents::POST_LOAD_FIXTURES => ['loadFixtures', -20],
        ];
    }

    public function updateSchema(): void
    {
        $this->dbalConnection->executeStatement(CreateTestAppTableQuery::QUERY);
    }

    public function loadFixtures(): void
    {
        $this->generateAsymmetricKeysHandler->handle(new GenerateAsymmetricKeysCommand());
    }
}
