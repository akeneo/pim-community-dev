<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Ramsey\Uuid\Rfc4122\FieldsInterface;
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

        /** @var UuidInterface $value */
        $fields = $value->getFields();
        if (!$fields instanceof FieldsInterface || $fields->getVersion() !== 4) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ uuid }}', $value->toString())
                ->setParameter('{{ version }}', $fields instanceof FieldsInterface ? strval($fields->getVersion()): null)
                ->addViolation();
        }
    }
}
