<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Factory\MetricFactory;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\CatalogBundle\PimCatalogBundle;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Manager\ProductMassActionManager;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Model\Metric;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;

/**
 * Edit common attributes of given products
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditCommonAttributes extends ProductMassEditOperation
{
    /** @var ArrayCollection */
    protected $values;

    /** @var ArrayCollection */
    protected $displayedAttributes;

    /** @var Locale */
    protected $locale;

    /** @var ProductManager */
    protected $productManager;

    /** @var ProductMassActionManager */
    protected $massActionManager;

    /** @var UserContext */
    protected $userContext;

    /** @var CatalogContext */
    protected $catalogContext;

    /** @var CurrencyManager */
    protected $currencyManager;

    /** @var array */
    protected $commonAttributes = array();

    /** @var ProductBuilder */
    protected $productBuilder;

    /** @var MetricFactory */
    protected $metricFactory;

    /** @var string */
    protected $productPriceClass;

    /** @var string */
    protected $productMediaClass;

    /**
     * Constructor
     *
     * @param ProductManager           $productManager
     * @param UserContext              $userContext
     * @param CurrencyManager          $currencyManager
     * @param CatalogContext           $catalogContext
     * @param ProductBuilder           $productBuilder
     * @param ProductMassActionManager $massActionManager
     * @param MetricFactory            $metricFactory
     * @param array                    $classes
     */
    public function __construct(
        ProductManager $productManager,
        UserContext $userContext,
        CurrencyManager $currencyManager,
        CatalogContext $catalogContext,
        ProductBuilder $productBuilder,
        ProductMassActionManager $massActionManager,
        MetricFactory $metricFactory,
        array $classes
    ) {
        $this->productManager = $productManager;
        $this->userContext = $userContext;
        $this->currencyManager = $currencyManager;
        $this->catalogContext = $catalogContext;
        $this->productBuilder = $productBuilder;
        $this->massActionManager = $massActionManager;
        $this->displayedAttributes = new ArrayCollection();
        $this->values = new ArrayCollection();
        $this->productPriceClass = $classes['product_price'];
        $this->productMediaClass = $classes['product_media'];
        $this->metricFactory = $metricFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function affectsCompleteness()
    {
        return true;
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
            'common_attributes' => $this->commonAttributes,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $productIds = array();
        $this->values = new ArrayCollection();
        foreach ($this->objects as $object) {
            $productIds[] = $object->getId();
        }
        $this->initializeCommonAttributes($productIds);

        foreach ($this->commonAttributes as $attribute) {
            $this->addValues($attribute);
        }
    }

    /**
     * Initializes self::commonAtributes with values from the repository
     * Attribute is not available for mass editing if:
     *   - it is an identifier
     *   - it is unique
     *   - without value AND not link to family
     *   - is not common to every products
     *
     * @param array $productIds
     */
    protected function initializeCommonAttributes(array $productIds)
    {
        // Set attribute options locale
        $currentLocaleCode = $this->getLocale()->getCode();
        $this->catalogContext->setLocaleCode($currentLocaleCode);

        // Get common attributes
        $attributes = $this->massActionManager->findCommonAttributes($productIds);

        foreach ($attributes as $attribute) {
            $attribute->setLocale($currentLocaleCode);
            $attribute->getGroup()->setLocale($currentLocaleCode);

            $this->commonAttributes[] = $attribute;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        parent::perform();

        $this->productManager->handleAllMedia($this->objects);
    }

    /**
     * {@inheritdoc}
     */
    protected function doPerform(ProductInterface $product)
    {
        $this->setProductValues($product);
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
        if (null === $productValue = $this->getProductValue($product, $value)) {
            $productValue = $this->productBuilder->addProductValue(
                $product,
                $value->getAttribute(),
                $value->getLocale(),
                $value->getScope()
            );
        }

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
     * Locale is not present because we current locale is bound at the same time as values during form submission
     *
     * @param AbstractAttribute $attribute
     */
    protected function addValues(AbstractAttribute $attribute)
    {
        $locale = $this->getLocale();
        if ($attribute->isScopable()) {
            foreach ($locale->getChannels() as $channel) {
                $key = $attribute->getCode().'_'.$channel->getCode();
                $this->values[$key] = $this->createValue($attribute, $locale->getCode(), $channel->getCode());
            }
        } else {
            $this->values[$attribute->getCode()] = $this->createValue($attribute, $locale->getCode());
        }
    }

    /**
     * Create a value
     *
     * @param AbstractAttribute $attribute
     * @param string            $localeCode
     * @param string            $channelCode
     *
     * @return ProductValueInterface
     */
    protected function createValue(AbstractAttribute $attribute, $localeCode = null, $channelCode = null)
    {
        $value = $this->productManager->createProductValue();
        $value->setAttribute($attribute);

        if ($attribute->isLocalizable()) {
            $value->setLocale($localeCode);
        }

        if ($attribute->isScopable()) {
            $value->setScope($channelCode);
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
        return new $this->productPriceClass(null, $currency);
    }

    /**
     * @param ProductValueInterface $productValue
     * @param ProductValueInterface $value
     */
    protected function setProductPrice(ProductValueInterface $productValue, ProductValueInterface $value)
    {
        foreach ($value->getPrices() as $price) {
            if (null === $productPrice = $productValue->getPrice($price->getCurrency())) {
                $this->productBuilder->addPriceForCurrency($productValue, $price->getCurrency());
            }
            $productPrice->setData($price->getData());
        }
    }

    /**
     * @param ProductValueInterface $productValue
     * @param ProductValueInterface $value
     */
    protected function setProductOption(ProductValueInterface $productValue, ProductValueInterface $value)
    {
        foreach ($productValue->getOptions() as $option) {
            if (!$value->getOptions()->contains($option)) {
                $productValue->removeOption($option);
            }
        }

        // TODO: Clean this code removing flush for ORM
        if (!class_exists(PimCatalogBundle::DOCTRINE_MONGODB)) {
            $this->productManager->getObjectManager()->flush();
        }

        foreach ($value->getOptions() as $option) {
            $productValue->addOption($option);
        }

    }

    /**
     * @param ProductValueInterface $productValue
     * @param ProductValueInterface $value
     */
    protected function setProductFile(ProductValueInterface $productValue, ProductValueInterface $value)
    {
        if (null === $media = $productValue->getMedia()) {
            $media = new $this->productMediaClass();
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
    protected function setProductMetric(ProductValueInterface $productValue, ProductValueInterface $value)
    {
        if (null === $metric = $productValue->getMetric()) {
            $metric = $this->metricFactory->createMetric($value->getAttribute()->getMetricFamily());
            $productValue->setMetric($metric);
        }
        $metric->setUnit($value->getMetric()->getUnit());
        $metric->setData($value->getMetric()->getData());
    }
}
