<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Settings\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Service\UpdateUserPermissionsInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Exception\ConstraintViolationListException;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionImage;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionLabel;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateConnectionHandler
{
    public function __construct(private ValidatorInterface $validator, private ConnectionRepositoryInterface $repository, private UpdateUserPermissionsInterface $updateUserPermissions)
    {
    }

    public function handle(UpdateConnectionCommand $command): void
    {
        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            throw new ConstraintViolationListException($violations);
        }

        $connection = $this->repository->findOneByCode($command->code());
        if (null === $connection) {
            throw new \InvalidArgumentException(
                \sprintf('Connection with code "%s" does not exist', $command->code())
            );
        }

        $connection->setLabel(new ConnectionLabel($command->label()));
        $connection->setFlowType(new FlowType($command->flowType()));
        $connection->setImage(null !== $command->image() ? new ConnectionImage($command->image()) : null);
        $command->auditable() ? $connection->enableAudit() : $connection->disableAudit();

        $this->updateUserPermissions->execute(
            $connection->userId(),
            (int) $command->userRoleId(),
            null === $command->userGroupId() ? null : (int) $command->userGroupId()
        );

        $this->repository->update($connection);
    }
}
