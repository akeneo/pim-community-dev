<?php

namespace Pim\Bundle\CatalogBundle\Builder;

use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Event\ProductEvents;
use Pim\Bundle\CatalogBundle\Manager\AttributeValuesResolver;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductPriceInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Repository\AssociationTypeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\CurrencyRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Product builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductBuilder implements ProductBuilderInterface
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    /** @var CurrencyRepositoryInterface */
    protected $currencyRepository;

    /** @var AssociationTypeRepositoryInterface */
    protected $assocTypeRepository;

    /** @var AttributeValuesResolver */
    protected $valuesResolver;

    /** @var string */
    protected $productClass;

    /** @var string */
    protected $productValueClass;

    /** @var string */
    protected $productPriceClass;

    /** @var string */
    protected $associationClass;

    /**
     * Constructor
     *
     * @param AttributeRepositoryInterface       $attributeRepository Attribute repository
     * @param FamilyRepositoryInterface          $familyRepository    Family repository
     * @param CurrencyRepositoryInterface        $currencyRepository  Currency repository
     * @param AssociationTypeRepositoryInterface $assocTypeRepository Association type repository
     * @param EventDispatcherInterface           $eventDispatcher     Event dispatcher
     * @param AttributeValuesResolver            $valuesResolver      Attributes values resolver
     * @param array                              $classes             Model classes
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        FamilyRepositoryInterface $familyRepository,
        CurrencyRepositoryInterface $currencyRepository,
        AssociationTypeRepositoryInterface $assocTypeRepository,
        EventDispatcherInterface $eventDispatcher,
        AttributeValuesResolver $valuesResolver,
        array $classes
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->familyRepository    = $familyRepository;
        $this->currencyRepository  = $currencyRepository;
        $this->assocTypeRepository = $assocTypeRepository;
        $this->eventDispatcher     = $eventDispatcher;
        $this->valuesResolver      = $valuesResolver;
        $this->productClass        = $classes['product'];
        $this->productValueClass   = $classes['product_value'];
        $this->productPriceClass   = $classes['product_price'];
        $this->associationClass    = $classes['association'];
    }

    /**
     * {@inheritdoc}
     */
    public function createProduct($identifier = null, $familyCode = null)
    {
        $product = new $this->productClass();

        $identifierAttribute = $this->attributeRepository->getIdentifier();
        $productValue = $this->createProductValue($identifierAttribute);
        $product->addValue($productValue);
        if (null !== $identifier) {
            $productValue->setData($identifier);
        }

        if (null !== $familyCode) {
            $family = $this->familyRepository->findOneByIdentifier($familyCode);
            $product->setFamily($family);
        }

        $event = new GenericEvent($product);
        $this->eventDispatcher->dispatch(ProductEvents::CREATE, $event);

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function addMissingProductValues(ProductInterface $product)
    {
        $attributes     = $this->getExpectedAttributes($product);
        $requiredValues = $this->valuesResolver->resolveEligibleValues($attributes);
        $existingValues = $this->getExistingValues($product);

        $missingValues = array_filter(
            $requiredValues,
            function ($value) use ($existingValues) {
                return !in_array($value, $existingValues);
            }
        );

        foreach ($missingValues as $value) {
            $this->addProductValue($product, $attributes[$value['attribute']], $value['locale'], $value['scope']);
        }

        $this->addMissingPricesToProduct($product);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addMissingAssociations(ProductInterface $product)
    {
        $missingAssocTypes = $this->assocTypeRepository->findMissingAssociationTypes($product);
        if (!empty($missingAssocTypes)) {
            foreach ($missingAssocTypes as $associationType) {
                $association = new $this->associationClass();
                $association->setAssociationType($associationType);
                $product->addAssociation($association);
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeToProduct(ProductInterface $product, AttributeInterface $attribute)
    {
        $requiredValues = $this->valuesResolver->resolveEligibleValues([$attribute]);

        foreach ($requiredValues as $value) {
            $this->addProductValue($product, $attribute, $value['locale'], $value['scope']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeAttributeFromProduct(ProductInterface $product, AttributeInterface $attribute)
    {
        foreach ($product->getValues() as $value) {
            if ($attribute === $value->getAttribute()) {
                $product->removeValue($value);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addPriceForCurrency(ProductValueInterface $value, $currency)
    {
        if (!$this->hasPriceForCurrency($value, $currency)) {
            $value->addPrice(new $this->productPriceClass(null, $currency));
        }

        return $this->getPriceForCurrency($value, $currency);
    }

    /**
     * {@inheritdoc}
     */
    public function addPriceForCurrencyWithData(ProductValueInterface $value, $currency, $data)
    {
        $price = $this->addPriceForCurrency($value, $currency);
        $price->setData($data);

        return $price;
    }

    /**
     * {@inheritdoc}
     */
    public function removePricesNotInCurrency(ProductValueInterface $value, array $currencies)
    {
        foreach ($value->getPrices() as $price) {
            if (!in_array($price->getCurrency(), $currencies)) {
                $value->removePrice($price);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addProductValue(
        ProductInterface $product,
        AttributeInterface $attribute,
        $locale = null,
        $scope = null
    ) {
        $value = $this->createProductValue($attribute, $locale, $scope);

        $product->addValue($value);

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function createProductValue(AttributeInterface $attribute, $locale = null, $scope = null)
    {
        $class = $this->getProductValueClass();

        $value = new $class();
        $value->setAttribute($attribute);
        if ($attribute->isLocalizable()) {
            if ($locale !== null) {
                $value->setLocale($locale);
            } else {
                throw new \InvalidArgumentException(
                    sprintf(
                        'A locale must be provided to create a value for the localizable attribute %s',
                        $attribute->getCode()
                    )
                );
            }
        }

        if ($attribute->isScopable()) {
            if ($scope !== null) {
                $value->setScope($scope);
            } else {
                throw new \InvalidArgumentException(
                    sprintf(
                        'A scope must be provided to create a value for the scopable attribute %s',
                        $attribute->getCode()
                    )
                );
            }
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function addMissingPrices(ProductValueInterface $value)
    {
        $activeCurrencyCodes = $this->currencyRepository->getActivatedCurrencyCodes();

        if (AttributeTypes::PRICE_COLLECTION === $value->getAttribute()->getAttributeType()) {
            $prices = $value->getPrices();

            foreach ($activeCurrencyCodes as $currencyCode) {
                if (null === $value->getPrice($currencyCode)) {
                    $this->addPriceForCurrency($value, $currencyCode);
                }
            }

            foreach ($prices as $price) {
                if (!in_array($price->getCurrency(), $activeCurrencyCodes)) {
                    $value->removePrice($price);
                }
            }
        }

        return $value;
    }

    /**
     * @param ProductValueInterface $value
     * @param string                $currency
     *
     * @return bool
     */
    protected function hasPriceForCurrency(ProductValueInterface $value, $currency)
    {
        foreach ($value->getPrices() as $price) {
            if ($currency === $price->getCurrency()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ProductValueInterface $value
     * @param string                $currency
     *
     * @return null|ProductPriceInterface
     */
    protected function getPriceForCurrency(ProductValueInterface $value, $currency)
    {
        foreach ($value->getPrices() as $price) {
            if ($currency === $price->getCurrency()) {
                return $price;
            }
        }

        return null;
    }

    /**
     * Get expected attributes for the product
     *
     * @param ProductInterface $product
     *
     * @return AttributeInterface[]
     */
    protected function getExpectedAttributes(ProductInterface $product)
    {
        $attributes = [];
        $productAttributes = $product->getAttributes();
        foreach ($productAttributes as $attribute) {
            $attributes[$attribute->getCode()] = $attribute;
        }

        if ($family = $product->getFamily()) {
            foreach ($family->getAttributes() as $attribute) {
                $attributes[$attribute->getCode()] = $attribute;
            }
        }

        return $attributes;
    }

    /**
     * Get product value class
     *
     * @return string
     */
    protected function getProductValueClass()
    {
        return $this->productValueClass;
    }

    /**
     * Returns an array of product values identifiers
     *
     * @param ProductInterface $product
     *
     * @return array:array
     */
    protected function getExistingValues(ProductInterface $product)
    {
        $existingValues = array();
        $values = $product->getValues();
        foreach ($values as $value) {
            $existingValues[] = array(
                'attribute' => $value->getAttribute()->getCode(),
                'type'      => $value->getAttribute()->getAttributeType(),
                'locale'    => $value->getLocale(),
                'scope'     => $value->getScope()
            );
        }

        return $existingValues;
    }

    /**
     * Add missing prices (a price per currency)
     *
     * @param ProductInterface $product the product
     */
    protected function addMissingPricesToProduct(ProductInterface $product)
    {
        foreach ($product->getValues() as $value) {
            $this->addMissingPrices($value);
        }
    }
}
