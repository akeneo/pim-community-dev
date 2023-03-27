<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductMapping;

use Akeneo\Catalogs\Application\Persistence\Channel\GetChannelQueryInterface;
use Akeneo\Catalogs\Application\Persistence\ReferenceEntity\FindOneReferenceEntityAttributeByIdentifierQueryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @phpstan-type ReferenceEntityAttributeSource array{sub_source: string, sub_scope: string|null, sub_locale: string|null}
 * @phpstan-import-type ReferenceEntityAttribute from FindOneReferenceEntityAttributeByIdentifierQueryInterface
 */
final class ReferenceEntityAttributeSourceContainsValidScopeValidator extends ConstraintValidator
{
    public function __construct(
        private FindOneReferenceEntityAttributeByIdentifierQueryInterface $findOneReferenceEntityAttributeByIdentifierQuery,
        private GetChannelQueryInterface $getChannelQuery,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ReferenceEntityAttributeSourceContainsValidScope) {
            throw new UnexpectedTypeException($constraint, ReferenceEntityAttributeSourceContainsValidScope::class);
        }

        if (!\is_array($value)) {
            throw new UnexpectedValueException($value, 'array');
        }

        /** @var ReferenceEntityAttributeSource $value */

        $referenceEntityAttribute = $this->findOneReferenceEntityAttributeByIdentifierQuery->execute($value['sub_source']);

        if (null === $referenceEntityAttribute) {
            throw new \LogicException('ReferenceEntity attribute not found');
        }

        $this->validateNonScopableSourceHasNoScope($referenceEntityAttribute, $value);
        $this->validateScopableSourceHasScope($referenceEntityAttribute, $value);
        $this->validateScopableSourceHasValidScope($referenceEntityAttribute, $value);
    }

    /**
     * @param ReferenceEntityAttribute $referenceEntityAttribute
     * @param ReferenceEntityAttributeSource $value
     */
    private function validateNonScopableSourceHasNoScope(array $referenceEntityAttribute, array $value): void
    {
        if ($referenceEntityAttribute['scopable'] || null === $value['sub_scope']) {
            return;
        }

        $this->context
            ->buildViolation('akeneo_catalogs.validation.product_mapping.source.sub_source.channel.not_empty')
            ->atPath('[sub_scope]')
            ->addViolation();
    }

    /**
     * @param ReferenceEntityAttribute $referenceEntityAttribute
     * @param ReferenceEntityAttributeSource $value
     */
    private function validateScopableSourceHasScope(array $referenceEntityAttribute, array $value): void
    {
        if (!$referenceEntityAttribute['scopable']) {
            return;
        }

        if (null === $value['sub_scope']) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.sub_source.channel.empty')
                ->atPath('[sub_scope]')
                ->addViolation();
        }
    }

    /**
     * @param ReferenceEntityAttribute $referenceEntityAttribute
     * @param ReferenceEntityAttributeSource $value
     */
    private function validateScopableSourceHasValidScope(array $referenceEntityAttribute, array $value): void
    {
        if (!$referenceEntityAttribute['scopable'] || null === $value['sub_scope']) {
            return;
        }

        $channel = $this->getChannelQuery->execute($value['sub_scope']);

        if (null === $channel) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.sub_source.channel.unknown')
                ->atPath('[sub_scope]')
                ->addViolation();
        }
    }
}
