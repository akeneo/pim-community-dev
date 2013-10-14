<?php

namespace Pim\Bundle\CatalogBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Pim\Bundle\CatalogBundle\Entity\VariantGroup;

/**
 * Subscriber that updates products inside the variant group
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BindVariantGroupProductsSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::SUBMIT => 'submit',
        );
    }

    /**
     * Add/remove products to/from the variant group
     *
     * @param FormEvent $event
     */
    public function submit(FormEvent $event)
    {
        $form         = $event->getForm();
        $variantGroup = $event->getData();

        $appendProducts = $form->get('appendProducts')->getData();
        $removeProducts = $form->get('removeProducts')->getData();

        $this->bindProducts($variantGroup, $appendProducts, $removeProducts);
    }

    /**
     * Bind products
     *
     * @param VariantGroup $variantGroup
     * @param array        $appendProducts
     * @param array        $removeProducts
     */
    private function bindProducts(VariantGroup $variantGroup, array $appendProducts, array $removeProducts)
    {
        foreach ($appendProducts as $product) {
            $variantGroup->addProduct($product);
            $product->setVariantGroup($variantGroup);
        }

        foreach ($removeProducts as $product) {
            $variantGroup->removeProduct($product);
            $product->setVariantGroup(null);
        }
    }
}
