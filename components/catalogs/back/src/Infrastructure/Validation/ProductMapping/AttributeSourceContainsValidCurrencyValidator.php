<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductMapping;

use Akeneo\Catalogs\Application\Persistence\Attribute\FindOneAttributeByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Currency\GetChannelCurrenciesQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Currency\IsCurrencyActivatedQueryInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @phpstan-import-type Attribute from FindOneAttributeByCodeQueryInterface
 * @phpstan-import-type SourceAssociation from Catalog
 */
class AttributeSourceContainsValidCurrencyValidator extends ConstraintValidator
{
    public function __construct(
        private readonly FindOneAttributeByCodeQueryInterface $findOneAttributeByCodeQuery,
        private readonly IsCurrencyActivatedQueryInterface $isCurrencyActivatedQuery,
        private readonly GetChannelCurrenciesQueryInterface $getChannelCurrenciesQuery,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof AttributeSourceContainsValidCurrency) {
            throw new UnexpectedTypeException($constraint, AttributeSourceContainsValidCurrency::class);
        }

        if (!\is_array($value)) {
            throw new UnexpectedValueException($value, 'array');
        }

        /** @var SourceAssociation $value */

        if (null === $value['source'] || !isset($value['parameters']) || !isset($value['parameters']['currency'])) {
            return;
        }

        $attribute = $this->findOneAttributeByCodeQuery->execute($value['source']);
        if (null === $attribute) {
            throw new \LogicException('Attribute not found');
        }

        $this->validateNonScopableSourceHasAValidCurrency($attribute, $value['parameters']['currency']);
        $this->validateScopableSourceHasAValidCurrency($attribute, $value['parameters']['currency'], $value['scope']);
    }

    /**
     * @param Attribute $attribute
     */
    private function validateNonScopableSourceHasAValidCurrency(array $attribute, string $currency): void
    {
        if ($attribute['scopable']) {
            return;
        }

        if (!$this->isCurrencyActivatedQuery->execute($currency)) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.currency.disabled')
                ->atPath('[parameters][currency]')
                ->addViolation();
        }
    }

    /**
     * @param Attribute $attribute
     */
    private function validateScopableSourceHasAValidCurrency(array $attribute, string $currency, ?string $scope): void
    {
        if (!$attribute['scopable'] || null === $scope) {
            return;
        }

        $channelCurrencies = $this->getChannelCurrenciesQuery->execute($scope);

        if (!\in_array($currency, $channelCurrencies, true)) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.currency.disabled')
                ->atPath('[parameters][currency]')
                ->addViolation();
        }
    }
}
