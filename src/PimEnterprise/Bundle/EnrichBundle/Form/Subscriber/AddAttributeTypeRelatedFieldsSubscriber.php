<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Form\Subscriber;

use Pim\Bundle\EnrichBundle\Form\Subscriber\AddAttributeTypeRelatedFieldsSubscriber as BaseAddAttributeTypeRelatedFieldsSubscriber;
use Symfony\Component\Form\FormEvent;

/**
 * Form subscriber for AttributeInterface
 * Allow to change field behavior like disable when editing
 *
 * @author Arnaud langlade <arnaud.langlade@akeneo.com>
 */
class AddAttributeTypeRelatedFieldsSubscriber extends BaseAddAttributeTypeRelatedFieldsSubscriber
{
    /**
     * {@inheritdoc}
     */
    public function preSetData(FormEvent $event)
    {
        parent::preSetData($event);

        $form = $event->getForm();

        $form->add('isEditable', 'switch', [
            'required' => false,
        ]);
    }
}
