<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Manager\ProductMassActionManager;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Edit common attributes of given products
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditCommonAttributes extends ProductMassEditOperation
{
    /** @var ArrayCollection|ProductValueInterface[] */
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

    /** @var string */
    protected $productPriceClass;

    /** @var string */
    protected $productMediaClass;

    /** @var ProductUpdaterInterface */
    protected $productUpdater;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * Constructor
     *
     * @param ProductManager           $productManager
     * @param ProductUpdaterInterface  $productUpdater
     * @param UserContext              $userContext
     * @param CurrencyManager          $currencyManager
     * @param CatalogContext           $catalogContext
     * @param ProductMassActionManager $massActionManager
     * @param NormalizerInterface      $normalizer
     * @param array                    $classes
     */
    public function __construct(
        ProductManager $productManager,
        ProductUpdaterInterface $productUpdater,
        UserContext $userContext,
        CurrencyManager $currencyManager,
        CatalogContext $catalogContext,
        ProductMassActionManager $massActionManager,
        NormalizerInterface $normalizer,
        array $classes
    ) {
        $this->productManager = $productManager;
        $this->productUpdater = $productUpdater;
        $this->userContext = $userContext;
        $this->currencyManager = $currencyManager;
        $this->catalogContext = $catalogContext;
        $this->massActionManager = $massActionManager;
        $this->displayedAttributes = new ArrayCollection();
        $this->values = new ArrayCollection();
        $this->productPriceClass = $classes['product_price'];
        $this->productMediaClass = $classes['product_media'];
        $this->normalizer = $normalizer;
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
            'current_locale' => $this->getLocale()->getCode()
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
        $attributes = $this->filterLocaleSpecificAttributes($attributes, $currentLocaleCode);

        foreach ($attributes as $attribute) {
            $attribute->setLocale($currentLocaleCode);
            $attribute->getGroup()->setLocale($currentLocaleCode);
            $this->commonAttributes[] = $attribute;
        }
    }

    /**
     * Filter the locale specific attributes
     *
     * @param AbstractAttribute[] $attributes
     * @param string              $currentLocaleCode
     *
     * @return boolean
     */
    protected function filterLocaleSpecificAttributes(array $attributes, $currentLocaleCode)
    {
        foreach ($attributes as $indAttribute => $attribute) {
            if ($attribute->getAvailableLocaleCodes()) {
                $availableCodes = $attribute->getAvailableLocaleCodes();
                if (!in_array($currentLocaleCode, $availableCodes)) {
                    unset($attributes[$indAttribute]);
                }
            }
        }

        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        parent::perform();
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

            $rawData = $this->normalizer->normalize($value->getData(), 'json');
            // if the value is localizable, let's use the locale the user has chosen in the form
            $locale = null !== $value->getLocale() ? $this->getLocale()->getCode() : null;

            $this->productUpdater->setValue(
                [$product],
                $value->getAttribute()->getCode(),
                $rawData,
                $locale,
                $value->getScope()
            );
        }
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
}
