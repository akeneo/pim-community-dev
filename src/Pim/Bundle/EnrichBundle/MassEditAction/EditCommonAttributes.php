<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Model\Media;
use Pim\Bundle\CatalogBundle\Model\Metric;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Edit common attributes of given products
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditCommonAttributes extends AbstractMassEditAction
{
    /**
     * @var ArrayCollection
     */
    protected $values;

    /**
     * @var Locale
     */
    protected $locale;

    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * @var UserContext
     */
    protected $userContext;

    /**
     * @var CurrencyManager
     */
    protected $currencyManager;

    /**
     * @var array
     */
    protected $commonAttributes = array();

    /**
     * @var ArrayCollection
     */
    protected $displayedAttributes;

    /**
     * Collection of the attributes for each family code
     * @var array $familiesAttributes
     */
    protected $familiesAttributes = array();

    /**
     * Constructor
     *
     * @param ProductManager  $productManager
     * @param UserContext     $userContext
     * @param CurrencyManager $currencyManager
     */
    public function __construct(
        ProductManager $productManager,
        UserContext $userContext,
        CurrencyManager $currencyManager
    ) {
        $this->productManager      = $productManager;
        $this->userContext         = $userContext;
        $this->currencyManager     = $currencyManager;
        $this->values              = new ArrayCollection();
        $this->displayedAttributes = new ArrayCollection();
    }

    /**
     * Set values
     *
     * @param Collection $values
     *
     * @return EditCommonAttributes
     */
    public function setValues(Collection $values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * Get values
     *
     * @return Collection
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Set locale
     *
     * @param Locale $locale
     *
     * @return EditCommonAttributes
     */
    public function setLocale(Locale $locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get locale
     *
     * @return Locale
     */
    public function getLocale()
    {
        if ($this->locale instanceof Locale) {
            return $this->locale;
        }

        return $this->userContext->getCurrentLocale();
    }

    /**
     * Set common attributes
     *
     * @param array $commonAttributes
     *
     * @return EditCommonAttributes
     */
    public function setCommonAttributes(array $commonAttributes)
    {
        $this->commonAttributes = $commonAttributes;

        return $this;
    }

    /**
     * Get common attributes
     *
     * @return array
     */
    public function getCommonAttributes()
    {
        return $this->commonAttributes;
    }

    /**
     * Set displayed attributes
     *
     * @param Collection $displayedAttributes
     *
     * @return EditCommonAttributes
     */
    public function setDisplayedAttributes(Collection $displayedAttributes)
    {
        $this->displayedAttributes = $displayedAttributes;

        return $this;
    }

    /**
     * Get displayed attributes
     *
     * @return Collection
     */
    public function getDisplayedAttributes()
    {
        return $this->displayedAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return 'pim_enrich_mass_edit_common_attributes';
    }

    /**
     * Get form options
     *
     * @return array
     */
    public function getFormOptions()
    {
        return array(
            'locales'          => $this->userContext->getUserLocales(),
            'commonAttributes' => $this->commonAttributes,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(QueryBuilder $qb)
    {
        $products = $qb->getQuery()->getResult();
        $this->initializeCommonAttributes($products);

        foreach ($this->commonAttributes as $attribute) {
            $this->addValues($attribute);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function perform(QueryBuilder $qb)
    {
        $products = $qb->getQuery()->getResult();
        foreach ($products as $product) {
            $this->setProductValues($product);
        }
        $this->productManager->handleAllMedia($products);
    }

    /**
     * Initializes self::commonAtributes with values from the repository
     * Attribute is not available for mass editing if:
     *   - it is an identifier
     *   - it is unique
     *   - it isn't set on one of the selected products
     *
     * @param array $products
     */
    protected function initializeCommonAttributes(array $products)
    {
        $currentLocaleCode = $this->getLocale()->getCode();
        $attributes = $this->productManager->getAttributeRepository()->findAllWithGroups();

        // Set attribute options locale
        $this->productManager->setLocale($currentLocaleCode);

        foreach ($attributes as $attribute) {
            if ('pim_catalog_identifier' !== $attribute->getAttributeType() && !$attribute->isUnique()) {
                $attribute->setLocale($currentLocaleCode);
                $attribute->getVirtualGroup()->setLocale($currentLocaleCode);

                $this->commonAttributes[] = $attribute;
            }
        }

        foreach ($products as $product) {
            foreach ($this->commonAttributes as $key => $attribute) {
                if (!$product->hasAttribute($attribute)
                    && !$this->isAttributeFromFamily($attribute, $product->getFamily())) {
                    unset($this->commonAttributes[$key]);
                }
            }
        }
    }

    /**
     * Predicate to know if attribute is part of product family or not
     *
     * @param AbstractAttribute $attribute
     * @param Family            $family
     *
     * @return boolean
     */
    protected function isAttributeFromFamily(AbstractAttribute $attribute, Family $family = null)
    {
        return $family !== null && $this->getFamilyAttributes($family)->contains($attribute);
    }

    /**
     * Get the attributes of a family
     *
     * @param Family $family
     *
     * @return AbstractAttribute[]
     */
    protected function getFamilyAttributes(Family $family)
    {
        $familyCode = $family->getCode();
        if (!isset($this->familiesAttributes[$familyCode])) {
            $this->familiesAttributes[$familyCode] = $family->getAttributes();
        }

        return $this->familiesAttributes[$familyCode];
    }

    /**
     * Set product values with the one stored inside $this->values
     *
     * @param ProductInterface $product
     */
    protected function setProductValues(ProductInterface $product)
    {
        foreach ($this->values as $value) {
            if ($this->displayedAttributes->contains($value->getAttribute())) {
                $this->setProductValue($product, $value);
            }
        }
    }

    /**
     * Set a product value
     *
     * @param ProductInterface      $product
     * @param ProductValueInterface $value
     */
    protected function setProductValue(ProductInterface $product, ProductValueInterface $value)
    {
        $productValue = $this->getProductValue($product, $value);

        switch ($value->getAttribute()->getAttributeType()) {
            case 'pim_catalog_price_collection':
                $this->setProductPrice($productValue, $value);

                break;
            case 'pim_catalog_multiselect':
                $this->setProductOption($productValue, $value);

                break;
            case 'pim_catalog_file':
            case 'pim_catalog_image':
                $this->setProductFile($productValue, $value);

                break;
            case 'pim_catalog_metric':
                $this->setProductMetric($productValue, $value);

                break;
            default:
                $productValue->setData($value->getData());
        }
    }

    /**
     * Get product value
     *
     * @param ProductInterface      $product
     * @param ProductValueInterface $value
     *
     * @return ProductValueInterface
     */
    protected function getProductValue(ProductInterface $product, ProductValueInterface $value)
    {
        return $product->getValue(
            $value->getAttribute()->getCode(),
            $value->getAttribute()->isLocalizable() ? $this->getLocale()->getCode() : null,
            $value->getAttribute()->isScopable() ? $value->getScope() : null
        );
    }

    /**
     * Add all the values required by the given attribute
     *
     * @param AbstractAttribute $attribute
     */
    protected function addValues(AbstractAttribute $attribute)
    {
        $locale = $this->getLocale();
        if ($attribute->isScopable()) {
            foreach ($locale->getChannels() as $channel) {
                $key = $attribute->getCode().'_'.$channel->getCode();
                $this->values[$key] = $this->createValue($attribute, $locale, $channel);
            }
        } else {
            $this->values[$attribute->getCode()] = $this->createValue($attribute, $locale);
        }
    }

    /**
     * Create a value
     *
     * @param AbstractAttribute $attribute
     * @param Locale            $locale
     * @param Channel           $channel
     *
     * @return ProductValueInterface
     */
    protected function createValue(AbstractAttribute $attribute, Locale $locale, Channel $channel = null)
    {
        $value = $this->productManager->createProductValue();
        $value->setAttribute($attribute);

        if ($attribute->isLocalizable()) {
            $value->setLocale($locale);
        }

        if ($channel && $attribute->isScopable()) {
            $value->setScope($channel->getCode());
        }

        if ('pim_catalog_price_collection' === $attribute->getAttributeType()) {
            foreach ($this->currencyManager->getActiveCodes() as $code) {
                $value->addPrice($this->createProductPrice($code));
            }
        }

        return $value;
    }

    /**
     * Create a price
     *
     * @param string $currency
     *
     * @return ProductPrice
     */
    protected function createProductPrice($currency)
    {
        return new ProductPrice(null, $currency);
    }

    /**
     * @param ProductValueInterface $productValue
     * @param ProductValueInterface $value
     */
    private function setProductPrice(ProductValueInterface $productValue, ProductValueInterface $value)
    {
        foreach ($value->getPrices() as $price) {
            if (false === $productPrice = $productValue->getPrice($price->getCurrency())) {
                // Add a new product price to the value if it wasn't defined before
                $productPrice = $this->createProductPrice($price->getCurrency());
                $productValue->addPrice($productPrice);
            }
            $productPrice->setData($price->getData());
        }
    }

    /**
     * @param ProductValueInterface $productValue
     * @param ProductValueInterface $value
     */
    private function setProductOption(ProductValueInterface $productValue, ProductValueInterface $value)
    {
        $productValue->getOptions()->clear();
        $this->productManager->getObjectManager()->flush();
        foreach ($value->getOptions() as $option) {
            $productValue->addOption($option);
        }
    }

    /**
     * @param ProductValueInterface $productValue
     * @param ProductValueInterface $value
     */
    private function setProductFile(ProductValueInterface $productValue, ProductValueInterface $value)
    {
        if (null === $media = $productValue->getMedia()) {
            $media = new Media();
            $productValue->setMedia($media);
        }
        $file = $value->getMedia()->getFile();
        if ($file) {
            $media->setFile($file);
        } else {
            $media->setRemoved(true);
        }
    }

    /**
     * @param ProductValueInterface $productValue
     * @param ProductValueInterface $value
     */
    private function setProductMetric(ProductValueInterface $productValue, ProductValueInterface $value)
    {
        if (null === $metric = $productValue->getMetric()) {
            $metric = new Metric();
            $metric->setFamily($value->getAttribute()->getMetricFamily());
            $productValue->setMetric($metric);
        }
        $metric->setUnit($value->getMetric()->getUnit());
        $metric->setData($value->getMetric()->getData());
    }
}
