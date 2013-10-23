<?php

namespace Pim\Bundle\CatalogBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Pim\Bundle\CatalogBundle\Entity\ProductAssociation;

/**
 * Subscriber that updates targets inside the ProductAssociation
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
     * Add/remove targets to/from the ProductAssociation
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
            $appendTargets = $child->get('appendTargets')->getData();
            $removeTargets = $child->get('removeTargets')->getData();

            $productAssociation = $productAssociations->filter(
                function ($productAssociation) use ($association) {
                    return $productAssociation->getAssociation() === $association;
                }
            )->first();

            $this->bindTargets($productAssociation, $appendTargets, $removeTargets);
        }
    }

    /**
     * Bind targets
     *
     * @param ProductAssociation $productAssociation
     * @param array              $appendTargets
     * @param array              $removeTargets
     *
     * @return null
     */
    private function bindTargets(ProductAssociation $productAssociation, array $appendTargets, array $removeTargets)
    {
        foreach ($appendTargets as $target) {
            $productAssociation->addTarget($target);
        }

        foreach ($removeTargets as $target) {
            $productAssociation->removeTarget($target);
        }
    }
}
