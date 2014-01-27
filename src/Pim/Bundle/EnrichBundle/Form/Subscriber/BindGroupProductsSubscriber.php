<?php

namespace Pim\Bundle\EnrichBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Pim\Bundle\CatalogBundle\Entity\Group;

/**
 * Subscriber that updates products inside the variant group
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BindGroupProductsSubscriber implements EventSubscriberInterface
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
        $form  = $event->getForm();
        $group = $event->getData();

        $appendProducts = $form->get('appendProducts')->getData();
        $removeProducts = $form->get('removeProducts')->getData();

        $this->bindProducts($group, $appendProducts, $removeProducts);
    }

    /**
     * Bind products
     *
     * @param Group $group
     * @param array $appendProducts
     * @param array $removeProducts
     */
    protected function bindProducts(Group $group, array $appendProducts, array $removeProducts)
    {
        foreach ($appendProducts as $product) {
            $group->addProduct($product);
        }

        foreach ($removeProducts as $product) {
            $group->removeProduct($product);
        }
    }
}
