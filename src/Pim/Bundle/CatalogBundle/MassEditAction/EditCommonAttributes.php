<?php

namespace Pim\Bundle\CatalogBundle\MassEditAction;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\FlexibleEntityBundle\Entity\Media;
use Oro\Bundle\FlexibleEntityBundle\Entity\Metric;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Entity\ProductPrice;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
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
     * @var LocaleManager
     */
    protected $localeManager;

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
    protected $attributesToDisplay;

    /**
     * Constructor
     *
     * @param ProductManager  $productManager
     * @param LocaleManager   $localeManager
     * @param CurrencyManager $currencyManager
     */
    public function __construct(
        ProductManager $productManager,
        LocaleManager $localeManager,
        CurrencyManager $currencyManager
    ) {
        $this->productManager      = $productManager;
        $this->localeManager       = $localeManager;
        $this->currencyManager     = $currencyManager;
        $this->values              = new ArrayCollection();
        $this->attributesToDisplay = new ArrayCollection();
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

        return $this->localeManager->getLocaleByCode(
            $this->productManager->getLocale()
        );
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
     * Set attributes to display
     *
     * @param Collection $attributesToDisplay
     *
     * @return EditCommonAttributes
     */
    public function setAttributesToDisplay(Collection $attributesToDisplay)
    {
        $this->attributesToDisplay = $attributesToDisplay;

        return $this;
    }

    /**
     * Get attributes to display
     *
     * @return Collection
     */
    public function getAttributesToDisplay()
    {
        return $this->attributesToDisplay;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return 'pim_catalog_mass_edit_common_attributes';
    }

    /**
     * Get form options
     *
     * @return array
     */
    public function getFormOptions()
    {
        return array(
            'locales'          => $this->localeManager->getUserLocales(),
            'commonAttributes' => $this->commonAttributes,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $products)
    {
        $this->initializeCommonAttributes();

        foreach ($products as $product) {
            foreach ($this->commonAttributes as $key => $attribute) {
                if ('pim_catalog_identifier' === $attribute->getAttributeType() ||
                    $attribute->getUnique() ||
                    false === $product->getValue($attribute->getCode())) {
                    /**
                     * Attribute is not available for mass editing if:
                     *   - it is unique
                     *   - it isn't set on one of the selected products
                     */
                    unset($this->commonAttributes[$key]);
                }
            }
        }

        foreach ($this->commonAttributes as $key => $attribute) {
            if ($this->attributesToDisplay->contains($attribute)) {
                $this->addValues($attribute);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function perform(array $products)
    {
        foreach ($products as $product) {
            $this->setProductValues($product);
        }
        $this->productManager->handleAllMedia($products);
        $this->productManager->saveAll($products, true);
    }

    /**
     * Initializes self::commonAtributes with values from the repository
     */
    protected function initializeCommonAttributes()
    {
        $attributes = $this->productManager->getAttributeRepository()->findAll();

        $currentLocaleCode = $this->getLocale()->getCode();

        // Set attribute options locale
        $this->productManager->setLocale($currentLocaleCode);

        foreach ($attributes as $attribute) {
            $attribute->setLocale($currentLocaleCode);

            $attribute
                ->getVirtualGroup()
                ->setLocale($currentLocaleCode);

            $this->commonAttributes[] = $attribute;
        }
    }

    /**
     * Set product values with the one stored inside $this->values
     *
     * @param ProductInterface $product
     */
    protected function setProductValues(ProductInterface $product)
    {
        foreach ($this->values as $value) {
            $this->setProductValue($product, $value);
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
        $productValue = $product->getValue(
            $value->getAttribute()->getCode(),
            $value->getAttribute()->getTranslatable() ? $this->getLocale()->getCode() : null,
            $value->getAttribute()->getScopable() ? $value->getScope() : null
        );

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
        $this->productManager->getStorageManager()->flush();
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
        $media->setFile($value->getMedia()->getFile());
    }

    /**
     * @param ProductValueInterface $productValue
     * @param ProductValueInterface $value
     */
    private function setProductMetric(ProductValueInterface $productValue, ProductValueInterface $value)
    {
        if (null === $metric = $productValue->getMetric()) {
            $metric = new Metric();
            $productValue->setMetric($metric);
        }
        $metric->setUnit($value->getMetric()->getUnit());
        $metric->setData($value->getMetric()->getData());
    }

    /**
     * Add all the values required by the given attribute
     *
     * @param ProductAttribute $attribute
     */
    protected function addValues(ProductAttribute $attribute)
    {
        $locale = $this->getLocale();
        if ($attribute->getScopable()) {
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
     * @param ProductAttribute $attribute
     * @param Locale           $locale
     * @param Channel          $channel
     *
     * @return ProductValue
     */
    protected function createValue(ProductAttribute $attribute, Locale $locale, Channel $channel = null)
    {
        $value = $this->productManager->createFlexibleValue();
        $value->setAttribute($attribute);

        if ($attribute->getTranslatable()) {
            $value->setLocale($locale);
        }

        if ($channel && $attribute->getScopable()) {
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
}
