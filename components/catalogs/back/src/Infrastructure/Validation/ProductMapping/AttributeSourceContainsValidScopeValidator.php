<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductMapping;

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
 * @phpstan-type AttributeSource array{source: string, scope: string|null, locale: string|null}
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
final class AttributeSourceContainsValidScopeValidator extends ConstraintValidator
{
    public function __construct(
        private FindOneAttributeByCodeQueryInterface $findOneAttributeByCodeQuery,
        private GetChannelQueryInterface $getChannelQuery,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof AttributeSourceContainsValidScope) {
            throw new UnexpectedTypeException($constraint, AttributeSourceContainsValidScope::class);
        }

        if (!\is_array($value)) {
            throw new UnexpectedValueException($value, 'array');
        }

        /** @var AttributeSource $value */

        $attribute = $this->findOneAttributeByCodeQuery->execute($value['source']);

        if (null === $attribute) {
            throw new \LogicException('Attribute not found');
        }

        $this->validateNonScopableSourceHasNoScope($attribute, $value);
        $this->validateScopableSourceHasScope($attribute, $value);
        $this->validateScopableSourceHasValidScope($attribute, $value);
    }

    /**
     * @param Attribute $attribute
     * @param AttributeSource $value
     */
    private function validateNonScopableSourceHasNoScope(array $attribute, array $value): void
    {
        if ($attribute['scopable'] || null === $value['scope']) {
            return;
        }

        $this->context
            ->buildViolation('akeneo_catalogs.validation.product_mapping.source.channel.not_empty')
            ->atPath('[scope]')
            ->addViolation();
    }

    /**
     * @param Attribute $attribute
     * @param AttributeSource $value
     */
    private function validateScopableSourceHasScope(array $attribute, array $value): void
    {
        if (!$attribute['scopable']) {
            return;
        }

        if (null === $value['scope']) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.channel.empty')
                ->atPath('[scope]')
                ->addViolation();
        }
    }

    /**
     * @param Attribute $attribute
     * @param AttributeSource $value
     */
    private function validateScopableSourceHasValidScope(array $attribute, array $value): void
    {
        if (!$attribute['scopable'] || null === $value['scope']) {
            return;
        }

        $channel = $this->getChannelQuery->execute($value['scope']);

        if (null === $channel) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.channel.unknown')
                ->atPath('[scope]')
                ->addViolation();
        }
    }
}
