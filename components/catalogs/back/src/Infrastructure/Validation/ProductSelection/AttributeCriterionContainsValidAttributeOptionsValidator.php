<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductSelection;

use Akeneo\Catalogs\Application\Persistence\Attribute\FindOneAttributeByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Attribute\GetAttributeOptionsByCodeQueryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @phpstan-type AttributeCriterion array{field: string, value: array<string>}
 * @phpstan-type Attribute array{code: string, label: string, type: string, scopable: bool, localizable: bool}
 */
final class AttributeCriterionContainsValidAttributeOptionsValidator extends ConstraintValidator
{
    public function __construct(
        private FindOneAttributeByCodeQueryInterface $findOneAttributeByCodeQuery,
        private GetAttributeOptionsByCodeQueryInterface $getAttributeOptionsByCodeQuery,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof AttributeCriterionContainsValidAttributeOptions) {
            throw new UnexpectedTypeException($constraint, AttributeCriterionContainsValidAttributeOptions::class);
        }

        if (!\is_array($value)) {
            throw new UnexpectedValueException($value, 'array');
        }

        /** @var AttributeCriterion $value */

        $attribute = $this->findOneAttributeByCodeQuery->execute($value['field']);

        if (null === $attribute && 'categories' !== $value['field']) {
            throw new \LogicException('Attribute not found');
        }

        $options = $value['value'];

        if ([] === $options) {
            return;
        }

        $chunks = \array_chunk($options, 50);

        foreach ($chunks as $codes) {
            $existingCodes = $this->getAttributeOptionsByCodeQuery->execute($value['field'], $codes);

            if (\count($existingCodes) !== \count($codes)) {
                $this->context
                    ->buildViolation('akeneo_catalogs.validation.product_selection.criteria.attribute_option.unknown')
                    ->atPath('[value]')
                    ->addViolation();

                break;
            }
        }
    }
}
