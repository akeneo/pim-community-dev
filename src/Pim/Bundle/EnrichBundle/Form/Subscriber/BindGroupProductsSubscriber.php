<?php

namespace Pim\Bundle\EnrichBundle\Form\Subscriber;

use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

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
     * Constructor
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

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
     * @param GroupInterface $group
     * @param array          $appendProducts
     * @param array          $removeProducts
     */
    protected function bindProducts(GroupInterface $group, array $appendProducts, array $removeProducts)
    {
        foreach ($appendProducts as $product) {
            $group->addProduct($product);
        }

        foreach ($removeProducts as $product) {
            $group->removeProduct($product);
        }
    }
}
