<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Builder;

use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Manager\AttributeValuesResolverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class EntityWithValuesBuilder implements EntityWithValuesBuilderInterface
{
    /** @var AttributeValuesResolverInterface */
    protected $valuesResolver;

    /** @var ValueFactory */
    protected $productValueFactory;

    /**
     * @param AttributeValuesResolverInterface $valuesResolver
     * @param ValueFactory                     $productValueFactory
     */
    public function __construct(
        AttributeValuesResolverInterface $valuesResolver,
        ValueFactory $productValueFactory
    ) {
        $this->valuesResolver = $valuesResolver;
        $this->productValueFactory = $productValueFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttribute(EntityWithValuesInterface $entityWithValues, AttributeInterface $attribute)
    {
        $requiredValues = $this->valuesResolver->resolveEligibleValues([$attribute]);

        foreach ($requiredValues as $value) {
            $this->addOrReplaceValue($entityWithValues, $attribute, $value['locale'], $value['scope'], null);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addOrReplaceValue(
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        ?string $localeCode,
        ?string $scopeCode,
        $data
    ) : ValueInterface {
        // TODO: TIP-722: This is a temporary fix, Product identifier should be used only as a field
        if (AttributeTypes::IDENTIFIER === $attribute->getType() && $entityWithValues instanceof ProductInterface) {
            $identifierValue = ScalarValue::value($attribute->getCode(), $data);
            $entityWithValues->setIdentifier($identifierValue);

            return $identifierValue;
        }

        $formerValue = $entityWithValues->getValue($attribute->getCode(), $localeCode, $scopeCode);
        $newValue = $this->productValueFactory->create($attribute, $scopeCode, $localeCode, $data);

        $shouldRemoveValue = ((is_string($data) && '' === trim($data)) || [] === $data || null === $data);
        if ($shouldRemoveValue && null !== $formerValue) {
            $entityWithValues->removeValue($formerValue);

            return $newValue;
        }

        $entityWithValues->addOrReplaceValue($newValue);

        return $newValue;
    }
}
