<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Builder;

use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Manager\AttributeValuesResolverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;

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

    /** @var GetAttributes */
    protected $getAttributesQuery;

    /**
     * @param AttributeValuesResolverInterface $valuesResolver
     * @param ValueFactory                     $productValueFactory
     */
    public function __construct(
        AttributeValuesResolverInterface $valuesResolver,
        ValueFactory $productValueFactory,
        GetAttributes $getAttributesQuery
    ) {
        $this->valuesResolver = $valuesResolver;
        $this->productValueFactory = $productValueFactory;
        $this->getAttributesQuery = $getAttributesQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function addOrReplaceValue(
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        ?string $localeCode,
        ?string $channelCode,
        $data
    ): ?ValueInterface {
        $attribute = $this->getAttributesQuery->forCode($attribute->getCode());

        $formerValue = $entityWithValues->getValue($attribute->code(), $localeCode, $channelCode);
        $isFormerValueFilled = null !== $formerValue && '' !== $formerValue && [] !== $formerValue;
        $isNewValueFilled = null !== $data && '' !== $data && [] !== $data;

        if (!$isFormerValueFilled && $isNewValueFilled) {
            return $this->createValue($entityWithValues, $attribute, $localeCode, $channelCode, $data);
        }

        if ($isFormerValueFilled && $isNewValueFilled) {
            return $this->updateValue($entityWithValues, $attribute, $localeCode, $channelCode, $data);
        }

        if ($isFormerValueFilled && !$isNewValueFilled) {
            return $this->removeValue($entityWithValues, $attribute, $localeCode, $channelCode, $data);
        }

        return null;
    }

    private function createValue(
        EntityWithValuesInterface $entityWithValues,
        Attribute $attribute,
        ?string $localeCode,
        ?string $channelCode,
        $data
    ): ValueInterface {
        $newValue = $this->productValueFactory->createByCheckingData($attribute, $channelCode, $localeCode, $data);
        $entityWithValues->addValue($newValue);
        $this->updateProductIdentiferIfNeeded($attribute, $entityWithValues, $data);

        return $newValue;
    }

    private function updateValue(
        EntityWithValuesInterface $entityWithValues,
        Attribute $attribute,
        ?string $localeCode,
        ?string $channelCode,
        $data
    ): ValueInterface {
        $formerValue = $entityWithValues->getValue($attribute->code(), $localeCode, $channelCode);
        $updatedValue = $this->productValueFactory->createByCheckingData($attribute, $channelCode, $localeCode, $data);
        $entityWithValues->removeValue($formerValue)->addValue($updatedValue);
        $this->updateProductIdentiferIfNeeded($attribute, $entityWithValues, $data);

        return $updatedValue;
    }

    private function removeValue(
        EntityWithValuesInterface $entityWithValues,
        Attribute $attribute,
        ?string $localeCode,
        ?string $channelCode,
        $data
    ): ?ValueInterface {
        $formerValue = $entityWithValues->getValue($attribute->code(), $localeCode, $channelCode);
        $entityWithValues->removeValue($formerValue);
        $this->updateProductIdentiferIfNeeded($attribute, $entityWithValues, $data);

        return null;
    }

    private function updateProductIdentiferIfNeeded(
        Attribute $attribute,
        EntityWithValuesInterface $entityWithValues,
        $data
    ): void {
        // TODO: TIP-722: This is a temporary fix, Product identifier should be used only as a field
        if (AttributeTypes::IDENTIFIER === $attribute->type() && $entityWithValues instanceof ProductInterface) {
            $entityWithValues->setIdentifier($data);
        }
    }
}
