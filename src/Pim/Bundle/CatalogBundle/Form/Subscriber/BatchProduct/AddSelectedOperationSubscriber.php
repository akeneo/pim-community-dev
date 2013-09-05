<?php

namespace Pim\Bundle\CatalogBundle\Form\Subscriber\BatchProduct;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Pim\Bundle\CatalogBundle\BatchOperation\BatchOperation;
use Pim\Bundle\CatalogBundle\Model\BatchProduct;

/**
 * Add selected operation field if one is set
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddSelectedOperationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData'
        );
    }

    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data) {
            return;
        }

        $operation = $data->getOperation();
        if ($operation instanceof BatchOperation) {
            $form
                ->remove('operationAlias')
                ->add('operation', $operation->getFormType());
        }
    }
}
