<?php

namespace Pim\Bundle\CatalogBundle\BatchOperation;

use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Form\Type\BatchOperation\EditCommonAttributesType;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;

/**
 * Edit common attributes of given products
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditCommonAttributes extends AbstractBatchOperation
{
    protected $values;

    protected $locale;

    protected $productManager;

    protected $localeManager;

    public $commonAttributes = array();

    public $attributesToDisplay;

    public function __construct(FlexibleManager $productManager, LocaleManager $localeManager)
    {
        $this->productManager      = $productManager;
        $this->localeManager       = $localeManager;
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

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return new EditCommonAttributesType();
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
    public function initialize(array $products, array $parameters)
    {
        $this->commonAttributes = $this->productManager->getAttributeRepository()->findAll();

        foreach ($products as $product) {
            foreach ($this->commonAttributes as $key => $attribute) {
                if ($attribute->getUnique() || false === $product->getValue($attribute->getCode())) {
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
    public function perform(array $products, array $parameters)
    {
        foreach ($products as $product) {
            foreach ($this->product as $value) {
                $product
                    ->getValue($value->getAttribute()->getCode(), $this->locale)
                    ->setData($value->getData());
            }
        }
        $this->productManager->getStorageManager()->flush();
    }

    private function addValues(ProductAttribute $attribute)
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

    private function createValue(ProductAttribute $attribute, Locale $locale, Channel $channel = null)
    {
        $value = $this->productManager->createFlexibleValue();
        $value->setAttribute($attribute);

        if ($attribute->getTranslatable()) {
            $value->setLocale($locale);
        }

        if ($channel && $attribute->getScopable()) {
            $value->setScope($channel->getCode());
        }

        return $value;
    }
}
