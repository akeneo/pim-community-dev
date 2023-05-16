<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Validation;

use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConnectionMustExistValidator extends ConstraintValidator
{
    public function __construct(private ConnectionRepositoryInterface $repository)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ConnectionMustExist) {
            throw new UnexpectedTypeException($constraint, ConnectionMustExist::class);
        }

        $connection = $this->repository->findOneByCode($value);
        if (null === $connection) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
