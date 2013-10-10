<?php

namespace Pim\Bundle\ImportExportBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Pim\Bundle\ImportExportBundle\Converter\ProductEnabledConverter;
use Pim\Bundle\ImportExportBundle\Converter\ProductValueConverter;
use Pim\Bundle\ImportExportBundle\Converter\ProductFamilyConverter;
use Pim\Bundle\ImportExportBundle\Converter\ProductCategoriesConverter;
use Pim\Bundle\ImportExportBundle\Converter\ProductVariantGroupConverter;

/**
 * Transform imported product data into a bindable data to the product form
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TransformImportedProductDataSubscriber implements EventSubscriberInterface
{
    /**
     * @var ProductEnabledConverter $enabledConverter
     */
    protected $enabledConverter;

    /**
     * @var ProductValueConverter $valueConverter
     */
    protected $valueConverter;

    /**
     * @var ProductFamilyConverter $familyConverter
     */
    protected $familyConverter;

    /**
     * @var ProductCategoriesConverter $categoriesConverter
     */
    protected $categoriesConverter;

    /**
     * @var ProductVariantGroupConverter $variantGroupConverter
     */
    protected $variantGroupConverter;

    /**
     * Constructor
     * @param ProductEnabledConverter      $enabledConverter
     * @param ProductValueConverter        $valueConverter
     * @param ProductFamilyConverter       $familyConverter
     * @param ProductCategoriesConverter   $categoriesConverter
     * @param ProductVariantGroupConverter $variantGroupConverter
     */
    public function __construct(
        ProductEnabledConverter $enabledConverter,
        ProductValueConverter $valueConverter,
        ProductFamilyConverter $familyConverter,
        ProductCategoriesConverter $categoriesConverter,
        ProductVariantGroupConverter $variantGroupConverter
    ) {
        $this->enabledConverter      = $enabledConverter;
        $this->valueConverter        = $valueConverter;
        $this->familyConverter       = $familyConverter;
        $this->categoriesConverter   = $categoriesConverter;
        $this->variantGroupConverter = $variantGroupConverter;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SUBMIT => 'preSubmit'
        );
    }

    /**
     * Transform the imported product data to allow binding them to the form
     *
     * @param FormEvent $event
     *
     * @return null
     */
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();

        $dataToSubmit = array_merge(
            $this->enabledConverter->convert($data),
            $this->valueConverter->convert($data),
            $this->familyConverter->convert($data),
            $this->categoriesConverter->convert($data),
            $this->variantGroupConverter->convert($data)
        );

        $event->setData($dataToSubmit);
    }
}
