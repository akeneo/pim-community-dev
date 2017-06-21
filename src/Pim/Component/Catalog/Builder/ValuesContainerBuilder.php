<?php

namespace Pim\Component\Catalog\Builder;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Factory\ValueFactory;
use Pim\Component\Catalog\Manager\AttributeValuesResolver;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValuesContainerInterface;

/**
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ValuesContainerBuilder implements ValuesContainerBuilderInterface
{
    /** @var AttributeValuesResolver */
    protected $valuesResolver;

    /** @var ValueFactory */
    protected $productValueFactory;

    public function __construct(
        AttributeValuesResolver $valuesResolver,
        ValueFactory $productValueFactory
    ) {
        $this->valuesResolver = $valuesResolver;
        $this->productValueFactory = $productValueFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttribute(ValuesContainerInterface $valuesContainer, AttributeInterface $attribute)
    {
        $requiredValues = $this->valuesResolver->resolveEligibleValues([$attribute]);

        foreach ($requiredValues as $value) {
            $this->addOrReplaceValue($valuesContainer, $attribute, $value['locale'], $value['scope'], null);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addOrReplaceValue(
        ValuesContainerInterface $valuesContainer,
        AttributeInterface $attribute,
        $locale,
        $scope,
        $data
    ) {
        $value = $valuesContainer->getValue($attribute->getCode(), $locale, $scope);
        if (null !== $value) {
            $valuesContainer->removeValue($value);
        }

        $value = $this->productValueFactory->create($attribute, $scope, $locale, $data);
        $valuesContainer->addValue($value);

        // TODO: TIP-722: This is a temporary fix, Product identifier should be used only as a field
        if (AttributeTypes::IDENTIFIER === $attribute->getType() && null !== $data) {
            $valuesContainer->setIdentifier($value);
        }

        return $value;
    }
}
