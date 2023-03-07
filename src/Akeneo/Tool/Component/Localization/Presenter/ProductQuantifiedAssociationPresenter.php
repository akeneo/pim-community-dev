<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Localization\Presenter;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindIdentifier;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductQuantifiedAssociationPresenter implements PresenterInterface
{
    public function __construct(
        private readonly FindIdentifier $findIdentifier,
        private readonly AssociationColumnsResolver $associationColumnsResolver
    ) {
    }

    /**
     * @param string $value
     * @param array $options
     * @return string
     */
    public function present($value, array $options = []): string
    {
        Assert::string($value);
        if (empty($value)) {
            return $value;
        }
        $values = explode(',', $value);
        $formattedValues = [];
        $validUuids = \array_filter($values, static fn (string $uuid): bool => Uuid::isValid($uuid));
        $identifiersFromUuids = $this->findIdentifier->fromUuids($validUuids);
        foreach ($values as $key) {
            $formattedValues[] = $identifiersFromUuids[$key]
                ?? (\in_array($key, $validUuids) ? sprintf('[%s]', $key) : $key);
        }

        return implode(',', $formattedValues);
    }

    /**
     * @param string $propertyName
     * @return bool
     */
    public function supports($propertyName): bool
    {
        $quantifiedAssociationNames = $this->associationColumnsResolver->resolveQuantifiedIdentifierAssociationColumns();
        $pattern = \sprintf('/%s$/', AssociationColumnsResolver::PRODUCT_ASSOCIATION_SUFFIX);

        return \in_array($propertyName, $quantifiedAssociationNames) && 1 === \preg_match($pattern, $propertyName);
    }
}
