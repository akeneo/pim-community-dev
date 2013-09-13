<?php

namespace Pim\Bundle\CatalogBundle\Form\Subscriber\MassEditAction;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Pim\Bundle\CatalogBundle\MassEditAction\MassEditAction;

/**
 * Add selected operation field if one is set
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddSelectedOperationSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_SET_DATA => 'postSetData'
        );
    }

    /**
     * @param FormEvent $event
     */
    public function postSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data) {
            return;
        }

        $operation = $data->getOperation();
        if ($operation instanceof MassEditAction) {
            $form
                ->remove('operationAlias')
                ->add('operation', $operation->getFormType(), $operation->getFormOptions());
        }
    }
}
