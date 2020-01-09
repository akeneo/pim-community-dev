<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Settings\Command;

use Akeneo\Connectivity\Connection\Domain\Settings\Exception\ConstraintViolationListException;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionImage;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionLabel;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateConnectionHandler
{
    /** @var ValidatorInterface */
    private $validator;

    /** @var ConnectionRepository */
    private $repository;

    public function __construct(ValidatorInterface $validator, ConnectionRepository $repository)
    {
        $this->validator = $validator;
        $this->repository = $repository;
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
                sprintf('Connection with code "%s" does not exist', $command->code())
            );
        }

        $connection->setLabel(new ConnectionLabel($command->label()));
        $connection->setFlowType(new FlowType($command->flowType()));
        $connection->setImage(null !== $command->image() ? new ConnectionImage($command->image()) : null);

        $this->repository->update($connection);
    }
}
