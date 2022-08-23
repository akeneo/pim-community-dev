<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductSelection;

use Akeneo\Catalogs\Application\Persistence\FindOneAttributeByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\GetAttributeOptionsByCodeQueryInterface;
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

        if (null === $attribute) {
            throw new \LogicException('Attribute not found');
        }

        $options = $value['value'];

        if ([] === $options) {
            return;
        }

        $page = 1;
        $limit = 50;

        while (\count($slice = \array_slice($options, ($page - 1) * $limit, $limit)) > 0) {
            $page++;

            if (\count($this->getAttributeOptionsByCodeQuery->execute($value['field'], $slice)) != \count($slice)) {
                $this->context
                    ->buildViolation('akeneo_catalogs.validation.product_selection.criteria.attribute_option.unknown')
                    ->atPath('[value]')
                    ->addViolation();

                break;
            }
        }
    }
}
