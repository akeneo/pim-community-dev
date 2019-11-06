<?php

declare(strict_types=1);

namespace Akeneo\Apps\Application\Command;

use Akeneo\Apps\Domain\Exception\ConstraintViolationListException;
use Akeneo\Apps\Domain\Model\ValueObject\AppLabel;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
use Akeneo\Apps\Domain\Persistence\Repository\AppRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateAppHandler
{
    /** @var ValidatorInterface */
    private $validator;
    /** @var AppRepository */
    private $repository;

    public function __construct(ValidatorInterface $validator, AppRepository $repository)
    {
        $this->validator = $validator;
        $this->repository = $repository;
    }

    public function handle(UpdateAppCommand $command): void
    {
        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            throw new ConstraintViolationListException($violations);
        }

        $app = $this->repository->findOneByCode($command->code());
        if (null === $app) {
            throw new \InvalidArgumentException(
                sprintf('App with code "%s" does not exist', $command->code())
            );
        }

        $app->setLabel(new AppLabel($command->label()));
        $app->setFlowType(new FlowType($command->flowType()));
        $this->repository->update($app);
    }
}
