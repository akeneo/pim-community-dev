<?php

namespace Pim\Component\Catalog\Builder;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Factory\ProductValueFactory;
use Pim\Component\Catalog\Manager\AttributeValuesResolver;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductPriceInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\ProductEvents;
use Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\CurrencyRepositoryInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
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

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var AttributeValuesResolver */
    protected $valuesResolver;

    /** @var string */
    protected $productClass;

    /** @var string */
    protected $productPriceClass;

    /** @var string */
    protected $associationClass;

    /** @var ProductValueFactory */
    protected $productValueFactory;

    /**
     * Constructor
     *
     * @param AttributeRepositoryInterface       $attributeRepository Attribute repository
     * @param FamilyRepositoryInterface          $familyRepository    Family repository
     * @param CurrencyRepositoryInterface        $currencyRepository  Currency repository
     * @param AssociationTypeRepositoryInterface $assocTypeRepository Association type repository
     * @param EventDispatcherInterface           $eventDispatcher     Event dispatcher
     * @param AttributeValuesResolver            $valuesResolver      Attributes values resolver
     * @param ProductValueFactory                $productValueFactory Product value factory
     * @param array                              $classes             Model classes
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        FamilyRepositoryInterface $familyRepository,
        CurrencyRepositoryInterface $currencyRepository,
        AssociationTypeRepositoryInterface $assocTypeRepository,
        EventDispatcherInterface $eventDispatcher,
        AttributeValuesResolver $valuesResolver,
        ProductValueFactory $productValueFactory,
        array $classes
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->familyRepository = $familyRepository;
        $this->currencyRepository = $currencyRepository;
        $this->assocTypeRepository = $assocTypeRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->valuesResolver = $valuesResolver;
        $this->productValueFactory = $productValueFactory;
        $this->productClass = $classes['product'];
        $this->productPriceClass = $classes['product_price'];
        $this->associationClass = $classes['association'];
    }

    /**
     * {@inheritdoc}
     */
    public function createProduct($identifier = null, $familyCode = null)
    {
        $product = new $this->productClass();

        $identifierAttribute = $this->attributeRepository->getIdentifier();
        $productValue = $this->addOrReplaceProductValue($product, $identifierAttribute, null, null);

        if (null !== $identifier) {
            $productValue->setData($identifier);
        }

        if (null !== $familyCode) {
            $family = $this->familyRepository->findOneByIdentifier($familyCode);
            $product->setFamily($family);
            $this->addBooleanToProduct($product);
        }

        $event = new GenericEvent($product);
        $this->eventDispatcher->dispatch(ProductEvents::CREATE, $event);

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function addMissingProductValues(ProductInterface $product, array $channels = null, array $locales = null)
    {
        $attributes = $this->getExpectedAttributes($product);
        $requiredValues = $this->valuesResolver->resolveEligibleValues($attributes, $channels, $locales);
        $existingValues = $this->getExistingValues($product);

        $missingValues = array_filter(
            $requiredValues,
            function ($value) use ($existingValues) {
                return !in_array($value, $existingValues);
            }
        );

        foreach ($missingValues as $value) {
            $this->addOrReplaceProductValue($product, $attributes[$value['attribute']], $value['locale'], $value['scope']);
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
            $this->addOrReplaceProductValue($product, $attribute, $value['locale'], $value['scope']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addPriceForCurrency(ProductValueInterface $value, $currency, $amount = null)
    {
        if (!$this->hasPriceForCurrency($value, $currency)) {
            $value->addPrice(new $this->productPriceClass(null, $currency));
        }

        $price = $this->getPriceForCurrency($value, $currency);
        $price->setData($amount);

        return $price;
    }

    /**
     * {@inheritdoc}
     */
    public function addPriceForCurrencyWithData(ProductValueInterface $value, $currency, $amount)
    {
        return $this->addPriceForCurrency($value, $currency, $amount);
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
        return $this->addOrReplaceProductValue($product, $attribute, $locale, $scope);
    }

    /**
     * {@inheritdoc}
     */
    public function addOrReplaceProductValue(
        ProductInterface $product,
        AttributeInterface $attribute,
        $locale = null,
        $scope = null
    ) {
        $productValue = $this->productValueFactory->create($attribute, $scope, $locale);
        $product->addValue($productValue);

        return $productValue;
    }

    /**
     * {@inheritdoc}
     */
    public function createProductValue(AttributeInterface $attribute, $locale = null, $scope = null)
    {
        return $this->productValueFactory->create($attribute, $scope, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function addMissingPrices(ProductValueInterface $value)
    {
        if (AttributeTypes::PRICE_COLLECTION === $value->getAttribute()->getType()) {
            $activeCurrencyCodes = $this->currencyRepository->getActivatedCurrencyCodes();
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
        $existingValues = [];
        $values = $product->getValues();
        foreach ($values as $value) {
            $existingValues[] = [
                'attribute' => $value->getAttribute()->getCode(),
                'type'      => $value->getAttribute()->getType(),
                'locale'    => $value->getLocale(),
                'scope'     => $value->getScope()
            ];
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

    /**
     * Set product values to "false" by default for every boolean attributes in the product's family.
     *
     * This workaround is due to the UI that does not manage null values for boolean attributes, only false or true.
     * It avoids to automatically submit boolean attributes belonging to the product's family in a proposal,
     * even if those boolean attributes were not modified by the user.
     *
     * FIXME : To remove when the UI will manage null values in boolean attributes (PIM-6056).
     *
     * @param ProductInterface $product
     */
    protected function addBooleanToProduct(ProductInterface $product)
    {
        $family = $product->getFamily();

        if (null === $family) {
            return;
        }

        foreach ($family->getAttributes() as $attribute) {
            if (AttributeTypes::BOOLEAN === $attribute->getType()) {
                $requiredValues = $this->valuesResolver->resolveEligibleValues([$attribute]);

                foreach ($requiredValues as $value) {
                    $productValue = $this->productValueFactory->create($attribute, $value['scope'], $value['locale']);
                    $productValue->setBoolean(false);
                    $product->addValue($productValue);
                }
            }
        }
    }
}
