<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsUuid4Validator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, IsUuid4::class);

        if (null === $value) {
            return;
        }

        Assert::isInstanceOf($value, UuidInterface::class);

        /** @var $value UuidInterface */
        if ($value->getVersion() !== 4) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ uuid }}', $value->toString())
                ->setParameter('{{ version }}', strval($value->getVersion()))
                ->addViolation();
        }
    }
}
