<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductSelection;

use Akeneo\Catalogs\Application\Persistence\Attribute\FindOneAttributeByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Channel\GetChannelQueryInterface;
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
 * @phpstan-type AttributeCriterion array{field: string, scope: string|null, locale: string|null}
 * @phpstan-type Attribute array{
 *    attribute_group_code: string,
 *    attribute_group_label: string,
 *    code: string,
 *    default_measurement_unit?: string,
 *    label: string,
 *    localizable: bool,
 *    measurement_family?: string,
 *    scopable: bool,
 *    type: string
 * }
 */
final class AttributeCriterionContainsValidScopeValidator extends ConstraintValidator
{
    public function __construct(
        private FindOneAttributeByCodeQueryInterface $findOneAttributeByCodeQuery,
        private GetChannelQueryInterface $getChannelQuery,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof AttributeCriterionContainsValidScope) {
            throw new UnexpectedTypeException($constraint, AttributeCriterionContainsValidScope::class);
        }

        if (!\is_array($value)) {
            throw new UnexpectedValueException($value, 'array');
        }

        /** @var AttributeCriterion $value */

        $attribute = $this->findOneAttributeByCodeQuery->execute($value['field']);

        if (null === $attribute) {
            throw new \LogicException('Attribute not found');
        }

        $this->validateNonScopableCriterionHasNoScope($attribute, $value);
        $this->validateScopableCriterionHasScope($attribute, $value);
        $this->validateScopableCriterionHasValidScope($attribute, $value);
    }

    /**
     * @param Attribute $attribute
     * @param AttributeCriterion $value
     */
    private function validateNonScopableCriterionHasNoScope(array $attribute, array $value): void
    {
        if ($attribute['scopable'] || null === $value['scope']) {
            return;
        }

        $this->context
            ->buildViolation('akeneo_catalogs.validation.product_selection.criteria.channel.not_empty')
            ->atPath('[scope]')
            ->addViolation();
    }

    /**
     * @param Attribute $attribute
     * @param AttributeCriterion $value
     */
    private function validateScopableCriterionHasScope(array $attribute, array $value): void
    {
        if (!$attribute['scopable']) {
            return;
        }

        if (null === $value['scope']) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_selection.criteria.channel.empty')
                ->atPath('[scope]')
                ->addViolation();
        }
    }

    /**
     * @param Attribute $attribute
     * @param AttributeCriterion $value
     */
    private function validateScopableCriterionHasValidScope(array $attribute, array $value): void
    {
        if (!$attribute['scopable'] || null === $value['scope']) {
            return;
        }

        $channel = $this->getChannelQuery->execute($value['scope']);

        if (null === $channel) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_selection.criteria.channel.unknown')
                ->atPath('[scope]')
                ->addViolation();
        }
    }
}
