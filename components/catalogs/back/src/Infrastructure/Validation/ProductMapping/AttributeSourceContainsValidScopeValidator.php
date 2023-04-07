<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductMapping;

use Akeneo\Catalogs\Application\Persistence\Attribute\FindOneAttributeByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Channel\GetChannelQueryInterface;
use Akeneo\Catalogs\Domain\Catalog;
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
 * @phpstan-import-type SourceAssociation from Catalog
 * @phpstan-import-type Attribute from FindOneAttributeByCodeQueryInterface
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

        /** @var SourceAssociation $value */

        if (null === $value['source']) {
            return;
        }

        $attribute = $this->findOneAttributeByCodeQuery->execute($value['source']);

        if (null === $attribute) {
            throw new \LogicException('Attribute not found');
        }

        $this->validateNonScopableSourceHasNoScope($attribute, $value['scope']);
        $this->validateScopableSourceHasScope($attribute, $value['scope']);
        $this->validateScopableSourceHasValidScope($attribute, $value['scope']);
    }

    /**
     * @param Attribute $attribute
     */
    private function validateNonScopableSourceHasNoScope(array $attribute, ?string $scope): void
    {
        if ($attribute['scopable'] || null === $scope) {
            return;
        }

        $this->context
            ->buildViolation('akeneo_catalogs.validation.product_mapping.source.channel.not_empty')
            ->atPath('[scope]')
            ->addViolation();
    }

    /**
     * @param Attribute $attribute
     */
    private function validateScopableSourceHasScope(array $attribute, ?string $scope): void
    {
        if (!$attribute['scopable']) {
            return;
        }

        if (null === $scope) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.channel.empty')
                ->atPath('[scope]')
                ->addViolation();
        }
    }

    /**
     * @param Attribute $attribute
     */
    private function validateScopableSourceHasValidScope(array $attribute, ?string $scope): void
    {
        if (!$attribute['scopable'] || null === $scope) {
            return;
        }

        $channel = $this->getChannelQuery->execute($scope);

        if (null === $channel) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.channel.unknown')
                ->atPath('[scope]')
                ->addViolation();
        }
    }
}
