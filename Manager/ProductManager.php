<?php

namespace Pim\Bundle\ProductBundle\Manager;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\ProductBundle\Entity\Product;
use Pim\Bundle\ProductBundle\Entity\ProductValue;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductManager extends FlexibleManager
{
    protected $mediaManager;

    public function __construct($flexibleName, $flexibleConfig, ObjectManager $storageManager, EventDispatcherInterface $eventDispatcher, AttributeTypeFactory $attributeTypeFactory, $mediaManager)
    {
        parent::__construct($flexibleName, $flexibleConfig, $storageManager, $eventDispatcher, $attributeTypeFactory);

        $this->mediaManager = $mediaManager;
    }

    /**
     * Save a product in two phases :
     *   1) Persist and flush the entity as usual
     *   2)
     *     2.1) Force the reloading of the object (to be sure all values are loaded)
     *     2.2) Add the missing translatable attribute locale values
     *     2.3) Reflush to save these new values
     */
    public function save(Product $product)
    {
        $this->handleMedia($product);
        $this->storageManager->persist($product);
        $this->storageManager->flush();

        $this->storageManager->refresh($product);
        $this->addMissingLocaleValues($product);
        $this->storageManager->flush();
    }

    /**
     * Add missing translatable attribute locale value
     *
     * It makes sure that if an attribute is translatable, then all values
     * in the locales defined by the entity activated languages exist.
     *
     * For example:
     *   An entity has french and english languages activated.
     *   It has a translatable attribute "name" with a value in french,
     *   but the value in english is not available.
     *   This method will create this value with an empty data.
     */
    private function addMissingLocaleValues(Product $product)
    {
        $values         = $product->getValues();
        $languages      = $product->getLanguages();
        $attributes     = array();
        $missingLocales = array();

        foreach ($values as $value) {
            $attribute = $value->getAttribute();
            $attributes[$attribute->getCode()] = $attribute;
            if (true === $attribute->getTranslatable()) {
                if (!isset($missingLocales[$attribute->getCode()])) {
                    $missingLocales[$attribute->getCode()] = $languages->map(function ($language) {
                        return $language->getCode();
                    })->toArray();
                }

                foreach ($languages as $language) {
                    if ($language->getCode() === $value->getLocale()) {
                        $missingLocales[$attribute->getCode()] = array_diff($missingLocales[$attribute->getCode()], array($value->getLocale()));
                    }
                }
            }
        }

        foreach ($missingLocales as $attribute => $locales) {
            foreach ($locales as $locale) {
                $value = new ProductValue;
                $value->setLocale($locale);
                $value->setAttribute($attributes[$attribute]);

                $product->addValue($value);
            }
        }
    }

    private function handleMedia(Product $product)
    {
        foreach ($product->getValues() as $value) {
            if (null !== $media = $value->getMedia()) {
                $this->mediaManager->handle(
                    $value->getMedia(),
                    null !== $media->getFile() ? $this->generateFilenamePrefix($product, $value) : null
                );
                if ($media->isRemoved() || null === $media->getFile()) {
                    $this->storageManager->remove($media);
                    $value->setMedia(null);
                }
            }
        }
    }

    private function generateFilenamePrefix(Product $product, ProductValue $value)
    {
        return sprintf(
            '%s-%s-%s-%s-%s',
            $product->getSku(),
            $value->getAttribute()->getCode(),
            $value->getLocale(),
            $value->getScope(),
            time()
        );
    }
}

