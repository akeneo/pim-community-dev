<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\ReferenceEntityProperty;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ReferenceEntityPropertyShouldBeValidValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function validate($property, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, ReferenceEntityPropertyShouldBeValid::class);

        if (!\is_array($property)) {
            return;
        }

        if (ReferenceEntityProperty::type() !== ($property['type'] ?? null)) {
            return;
        }

        if (!\array_key_exists('process', $property)) {
            $this->context
                ->buildViolation($constraint->fieldsRequired, [
                    '{{ field }}' => 'process',
                ])
                ->addViolation();

            return;
        }

        if (!\array_key_exists('attributeCode', $property)) {
            $this->context
                ->buildViolation($constraint->fieldsRequired, [
                    '{{ field }}' => 'attributeCode',
                ])
                ->addViolation();

            return;
        }

        $this->validator->inContext($this->context)->validate($property, [new Collection([
            'type' => null,
            'process' => [new PropertyProcessShouldBeValid()],
            'attributeCode' => [
                new Type('string'),
                new AttributeShouldExist(),
                new AttributeShouldHaveType(['type' => 'akeneo_reference_entity']),
            ],
            'scope' => [new Optional()],
            'locale' => [new Optional()],
        ]), new ScopeAndLocaleShouldBeValid()]);
    }
}
