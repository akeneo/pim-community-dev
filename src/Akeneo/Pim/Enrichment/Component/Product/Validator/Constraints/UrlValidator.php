<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\UrlValidator as BaseUrlValidator;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UrlValidator extends BaseUrlValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Url) {
            throw new UnexpectedTypeException($constraint, Url::class);
        }

        parent::validate($value, $constraint);
        foreach ($this->context->getViolations() as $key => $violation) {
            /* @var ConstraintViolationInterface $violation */
            if (Url::INVALID_URL_ERROR === $violation->getCode()) {
                $this->context->getViolations()->remove($key);
                $this->context->buildViolation($constraint->message)
                    ->setParameter('%attribute%', $constraint->attributeCode)
                    ->setInvalidValue($value)
                    ->setCode(Url::INVALID_URL_ERROR)
                    ->addViolation();
            }
        }
    }
}
