<?php

namespace Pim\Bundle\CatalogBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Pim\Bundle\CatalogBundle\CatalogEvents;
use Pim\Bundle\CatalogBundle\Event\FilterProductEvent;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Aims to add all values / required values when create or load a new product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InitializeValuesListener implements EventSubscriberInterface
{
    /**
     * Specifies the list of events to listen
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            CatalogEvents::CREATE_PRODUCT => array('onCreateProduct'),
        );
    }

    /**
     * Add values for each attribute
     *
     * @param FilterProductEvent $event
     */
    public function onCreateProduct(FilterProductEvent $event)
    {
        $product = $event->getProduct();
        $manager = $event->getProductManager();

        $findBy = ['required' => true];
        $attributes = $manager->getAttributeRepository()->findBy($findBy);
        $this->addValues($manager, $product, $attributes);
    }

    /**
     * @param ProductManager   $manager    the product manager
     * @param ProductInterface $product    the entity
     * @param array            $attributes the attributes
     */
    protected function addValues(ProductManager $manager, ProductInterface $product, $attributes)
    {
        foreach ($attributes as $attribute) {
            $value = $manager->createProductValue();
            $value->setAttribute($attribute);
            if ($attribute->getDefaultValue() !== null) {
                $value->setData($attribute->getDefaultValue());
            }
            $product->addValue($value);
        }
    }
}
