<?php

namespace Pim\Bundle\CatalogBundle\MassEditAction;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Entity\ProductPrice;
use Pim\Bundle\CatalogBundle\Entity\Product;
use Pim\Bundle\CatalogBundle\Entity\ProductValue;

/**
 * Edit common attributes of given products
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
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
     * @var FlexibleManager
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

    public function __construct(
        FlexibleManager $productManager,
        LocaleManager $localeManager,
        CurrencyManager $currencyManager
    ) {
        $this->productManager      = $productManager;
        $this->localeManager       = $localeManager;
        $this->currencyManager     = $currencyManager;
        $this->values              = new ArrayCollection();
        $this->attributesToDisplay = new ArrayCollection();
    }

    public function setValues(Collection $values)
    {
        $this->values = $values;

        return $this;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function setLocale(Locale $locale)
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLocale()
    {
        if ($this->locale instanceof Locale) {
            return $this->locale;
        }

        return $this->localeManager->getLocaleByCode(
            $this->productManager->getLocale()
        );
    }

    public function setCommonAttributes(array $commonAttributes)
    {
        $this->commonAttributes = $commonAttributes;

        return $this;
    }

    public function getCommonAttributes()
    {
        return $this->commonAttributes;
    }

    public function setAttributesToDisplay(Collection $attributesToDisplay)
    {
        $this->attributesToDisplay = $attributesToDisplay;

        return $this;
    }

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

    public function getFormOptions()
    {
        return array(
            'locales'          => $this->localeManager->getActiveLocales(),
            'commonAttributes' => $this->commonAttributes,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $products)
    {
        $this->commonAttributes = $this->productManager->getAttributeRepository()->findAll();

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

        $this->productManager->getStorageManager()->flush();
    }

    /**
     * Set product values with the one stored inside $this->values
     *
     * @param Product $product
     */
    protected function setProductValues(Product $product)
    {
        foreach ($this->values as $value) {
            $this->setProductValue($product, $value);
        }
    }

    /**
     * Set a product value
     *
     * @param Product      $product
     * @param ProductValue $value
     */
    protected function setProductValue(Product $product, ProductValue $value)
    {
        $productValue = $product->getValue(
            $value->getAttribute()->getCode(),
            $this->getLocale()->getCode(),
            $value->getScope()
        );

        if ('pim_catalog_price_collection' === $value->getAttribute()->getAttributeType()) {
            foreach ($value->getPrices() as $price) {
                if ($productPrice = $productValue->getPrice($price->getCurrency())) {
                    $productPrice->setData($price->getData());
                }
            }
        } else {
            $productValue->setData($value->getData());
        }
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
