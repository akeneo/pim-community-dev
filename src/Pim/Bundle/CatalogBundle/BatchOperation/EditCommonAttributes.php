<?php

namespace Pim\Bundle\CatalogBundle\BatchOperation;

use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Form\Type\BatchOperation\EditCommonAttributesType;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Product;

/**
 * Edit common product of given products
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditCommonAttributes extends AbstractBatchOperation
{
    protected $product;

    protected $locale;

    protected $productManager;

    protected $localeManager;

    public function __construct(FlexibleManager $productManager, LocaleManager $localeManager)
    {
        $this->productManager = $productManager;
        $this->localeManager  = $localeManager;
        $this->product        = $this->initializeProduct();
    }

    public function setProduct(Product $product)
    {
        $this->product = $product;

        return $this;
    }

    public function getProduct()
    {
        return $this->product;
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
        $locales = $this->localeManager->getActiveLocales();

        return array('locales' => $locales);
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $products, array $parameters)
    {
        $displayedAttributes = $this->getParameter('attributes', $parameters, array());
        $availableAttributes = $this->productManager->getAttributeRepository()->findByCode($displayedAttributes);

        foreach ($products as $product) {
            foreach ($availableAttributes as $key => $attribute) {
                if ($attribute->getUnique() || false === $product->getValue($attribute->getCode())) {
                    unset($availableAttributes[$key]);
                }
            }
        }

        foreach ($availableAttributes as $attribute) {
            if (in_array($attribute->getCode(), $displayedAttributes)) {
                $this->addValuesToProduct($attribute);
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

    private function addValuesToProduct(ProductAttribute $attribute)
    {
        $locale = $this->getLocale();
        if ($attribute->getScopable()) {
            foreach ($locale->getChannels() as $channel) {
                $this->product->addValue($this->createValue($attribute, $locale, $channel));
            }
        } else {
            $this->product->addValue($this->createValue($attribute, $locale));
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

    private function initializeProduct()
    {
        $product = $this->productManager->createFlexible();
        $product->removeValue($product->getIdentifier());

        return $product;
    }
}
