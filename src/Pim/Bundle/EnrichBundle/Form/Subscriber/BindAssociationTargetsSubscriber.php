<?php

namespace Pim\Bundle\EnrichBundle\Form\Subscriber;

use Pim\Component\Catalog\Model\AbstractAssociation;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Subscriber that updates target entities inside the Association
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BindAssociationTargetsSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::SUBMIT => 'submit',
        ];
    }

    /**
     * Add/remove target entities to/from the Association
     *
     * @param FormEvent $event
     */
    public function submit(FormEvent $event)
    {
        $form = $event->getForm();
        $associations = $event->getData();

        for ($count = $form->count(), $i = 0; $count > $i; $i++) {
            $child = $form->get($i);

            $associationType = $child->get('associationType')->getData();
            $appendProducts = $child->get('appendProducts')->getData();
            $removeProducts = $child->get('removeProducts')->getData();
            $appendGroups = $child->get('appendGroups')->getData();
            $removeGroups = $child->get('removeGroups')->getData();

            $association = $associations->filter(
                function ($association) use ($associationType) {
                    return $association->getAssociationType() === $associationType;
                }
            )->first();

            $this->bindTargets($association, $appendProducts, $removeProducts, $appendGroups, $removeGroups);
        }
    }

    /**
     * Bind target entities
     *
     * @param AbstractAssociation $association
     * @param ProductInterface[]  $appendProducts
     * @param ProductInterface[]  $removeProducts
     * @param Group[]             $appendGroups
     * @param Group[]             $removeGroups
     */
    protected function bindTargets(
        AbstractAssociation $association,
        array $appendProducts,
        array $removeProducts,
        array $appendGroups,
        array $removeGroups
    ) {
        foreach ($appendProducts as $product) {
            $association->addProduct($product);
        }

        foreach ($removeProducts as $product) {
            $association->removeProduct($product);
        }

        foreach ($appendGroups as $group) {
            $association->addGroup($group);
        }

        foreach ($removeGroups as $group) {
            $association->removeGroup($group);
        }
    }
}
