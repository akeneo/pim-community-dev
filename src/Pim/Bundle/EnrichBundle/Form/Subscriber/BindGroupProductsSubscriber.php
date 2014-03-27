<?php

namespace Pim\Bundle\EnrichBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\ProductRepositoryInterface;

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
     * @param Group $group
     * @param array $appendProducts
     * @param array $removeProducts
     */
    protected function bindProducts(Group $group, array $appendProducts, array $removeProducts)
    {
        foreach ($appendProducts as $product) {
            $product->addGroup($group);
        }

        foreach ($removeProducts as $product) {
            $product->removeGroup($group);
        }

        $this->updateProducts($appendProducts, $removeProducts);
    }

    /**
     * Update products, used in case of MongoDB
     *
     * @param array $appendProducts
     * @param array $removeProducts
     */
    protected function updateProducts($appendProducts, $removeProducts)
    {
        $documentManager = method_exists($this->productRepository, 'getDocumentManager') ?
            $this->productRepository->getDocumentManager() : null;
        $products = $appendProducts + $removeProducts;

        if ($documentManager && count($products)) {
            foreach ($removeProducts as $product) {
                $documentManager->persist($product);
            }
            $documentManager->flush();
        }
    }
}
