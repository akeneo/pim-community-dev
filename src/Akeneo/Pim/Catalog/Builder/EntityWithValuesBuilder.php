<?php

namespace Pim\Component\Catalog\Builder;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Factory\ValueFactory;
use Pim\Component\Catalog\Manager\AttributeValuesResolverInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Model\ProductInterface;

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
        $locale,
        $scope,
        $data
    ) {
        $value = $entityWithValues->getValue($attribute->getCode(), $locale, $scope);
        if (null !== $value) {
            $entityWithValues->removeValue($value);
        }

        $value = $this->productValueFactory->create($attribute, $scope, $locale, $data);
        $entityWithValues->addValue($value);

        // TODO: TIP-722: This is a temporary fix, Product identifier should be used only as a field
        if (AttributeTypes::IDENTIFIER === $attribute->getType() &&
            null !== $data &&
            $entityWithValues instanceof ProductInterface
        ) {
            $entityWithValues->setIdentifier($value);
        }

        return $value;
    }
}
