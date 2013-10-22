<?php

namespace Pim\Bundle\CatalogBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Pim\Bundle\CatalogBundle\Entity\ProductAssociation;

/**
 * Subscriber that updates target entities inside the ProductAssociation
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BindProductAssociationTargetsSubscriber implements EventSubscriberInterface
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
     * Add/remove target entities to/from the ProductAssociation
     *
     * @param FormEvent $event
     *
     * @return null
     */
    public function submit(FormEvent $event)
    {
        $form                = $event->getForm();
        $productAssociations = $event->getData();

        for ($count = $form->count(), $i = 0; $count > $i; $i++) {
            $child = $form->get($i);

            $association   = $child->get('association')->getData();
            $appendProducts = $child->get('appendProducts')->getData();
            $removeProducts = $child->get('removeProducts')->getData();

            $productAssociation = $productAssociations->filter(
                function ($productAssociation) use ($association) {
                    return $productAssociation->getAssociation() === $association;
                }
            )->first();

            $this->bindTargets($productAssociation, $appendProducts, $removeProducts);
        }
    }

    /**
     * Bind target entities
     *
     * @param ProductAssociation $productAssociation
     * @param array              $appendProducts
     * @param array              $removeProducts
     *
     * @return null
     */
    private function bindTargets(ProductAssociation $productAssociation, array $appendProducts, array $removeProducts)
    {
        foreach ($appendProducts as $target) {
            $productAssociation->addProduct($target);
        }

        foreach ($removeProducts as $target) {
            $productAssociation->removeProduct($target);
        }
    }
}
