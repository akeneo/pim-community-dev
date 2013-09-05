<?php

namespace Pim\Bundle\CatalogBundle\BatchOperation;

use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Form\Type\BatchOperation\EditCommonAttributesType;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;

/**
 * Edit common values of given products
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

    public function __construct(FlexibleManager $productManager, LocaleManager $localeManager)
    {
        $this->values         = new ArrayCollection();
        $this->productManager = $productManager;
        $this->localeManager  = $localeManager;
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

    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLocale()
    {
        return $this->locale;
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

        return array('locales' => array_combine($locales, $locales));
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $products)
    {
        $attributes = $this->productManager->getAttributeRepository()->findAll();

        foreach ($products as $product) {
            foreach ($attributes as $key => $attribute) {
                if ($attribute->getUnique() || false === $product->getValue($attribute->getCode())) {
                    unset($attributes[$key]);
                }
            }
        }

        foreach ($attributes as $attribute) {
            $value = $this->productManager->createFlexibleValue();
            $value->setAttribute($attribute);

            $this->values[$attribute->getCode()] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function perform(array $products)
    {
        foreach ($products as $product) {
            foreach ($this->values as $value) {
                $product
                    ->getValue($value->getAttribute()->getCode(), $this->locale)
                    ->setData($value->getData());
            }
        }
        $this->productManager->getStorageManager()->flush();
    }
}
