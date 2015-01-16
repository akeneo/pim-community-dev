<?php

namespace Pim\Bundle\EnrichBundle\Form\Subscriber;

use Pim\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Adds product template values to the variant group form
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddVariantGroupTemplateSubscriber implements EventSubscriberInterface
{
    /**
     * @param UserContext $userContext
     */
    public function __construct(UserContext $userContext)
    {
        $this->userContext = $userContext;
    }
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
        ];
    }

    /**
     * Adds product template to the form
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $group = $event->getData();

        if (null === $group || null === $group->getType() || !$group->getType()->isVariant()) {
            return;
        }

        $form = $event->getForm();
        $form->add(
            'productTemplate',
            'pim_enrich_product_template',
            [
                'currentLocale' => $this->userContext->getCurrentLocaleCode()
            ]
        );
    }
}
