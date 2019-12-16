<?php
declare(strict_types=1);

namespace Akeneo\Apps\Application\Validation\App;

use Akeneo\Apps\Domain\Persistence\Repository\AppRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CodeMustBeUniqueValidator extends ConstraintValidator
{
    /** @var AppRepository */
    private $repository;

    public function __construct(AppRepository $repository)
    {
        $this->repository = $repository;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (null !== $this->repository->findOneByCode($value)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
