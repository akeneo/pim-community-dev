<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection;

use Akeneo\Connectivity\Connection\Application\Settings\Service\DoesImageExistQueryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImageMustExistValidator extends ConstraintValidator
{
    /** @var DoesImageExistQueryInterface */
    private $doesImageExistQuery;

    public function __construct(DoesImageExistQueryInterface $doesImageExistQuery)
    {
        $this->doesImageExistQuery = $doesImageExistQuery;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ImageMustExist) {
            throw new UnexpectedTypeException($constraint, ImageMustExist::class);
        }

        if (null !== $value && !$this->doesImageExistQuery->execute($value)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
