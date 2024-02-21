<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByIdQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\UpdateConnectedAppDescriptionQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RefreshConnectedAppHandler
{
    public function __construct(
        private UpdateConnectedAppDescriptionQueryInterface $updateConnectedAppDescriptionQuery,
        private FindOneConnectedAppByIdQueryInterface $findOneConnectedAppByIdQuery,
        private GetAppQueryInterface $getAppQuery,
        private UserRepositoryInterface $userRepository,
        private ObjectUpdaterInterface $userUpdater,
        private SaverInterface $userSaver,
    ) {
    }

    public function handle(RefreshConnectedAppCommand $command): void
    {
        $connectedApp = $this->findOneConnectedAppByIdQuery->execute($command->getAppId());
        if (null === $connectedApp) {
            throw new \LogicException('Cannot refresh a non-exisiting connected app');
        }

        $app = $this->getAppQuery->execute($command->getAppId());
        if (null === $app) {
            throw new \LogicException('Cannot refresh a non-exisiting app');
        }

        $updatedConnectedApp = $connectedApp->withUpdatedDescription(
            $app->getName(),
            $app->getLogo(),
            $app->getAuthor(),
            $app->getCategories(),
            $app->isCertified(),
            $app->getPartner(),
        );

        $this->updateConnectedAppDescriptionQuery->execute($updatedConnectedApp);

        /** @var UserInterface|null */
        $connectedAppUser = $this->userRepository->findOneByIdentifier($connectedApp->getConnectionUsername());
        if (null === $connectedAppUser) {
            throw new \LogicException(\sprintf('User %s not found', $connectedApp->getConnectionUsername()));
        }

        $this->userUpdater->update($connectedAppUser, [
            'first_name' => \strtr($updatedConnectedApp->getName(), '<>&"', '____'),
        ]);
        $this->userSaver->save($connectedAppUser);
    }
}
