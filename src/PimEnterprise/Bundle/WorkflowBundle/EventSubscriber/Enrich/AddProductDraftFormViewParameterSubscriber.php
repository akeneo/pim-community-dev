<?php

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Enrich;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Pim\Bundle\EnrichBundle\Event\ProductEvents;
use PimEnterprise\Bundle\WorkflowBundle\Manager\ProductDraftManager;

/**
 * Add the proposition form view to the product edit template parameters
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AddProductDraftFormViewParameterSubscriber implements EventSubscriberInterface
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var ProductDraftManager */
    protected $manager;

    /**
     * @param FormFactoryInterface $formFactory
     * @param ProductDraftManager   $manager
     */
    public function __construct(FormFactoryInterface $formFactory, ProductDraftManager $manager)
    {
        $this->formFactory = $formFactory;
        $this->manager     = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductEvents::PRE_RENDER_EDIT => 'addPropositionFormView',
        ];
    }

    /**
     * Add a proposition form view parameter to the template parameters
     *
     * @param GenericEvent $event
     */
    public function addPropositionFormView(GenericEvent $event)
    {
        try {
            $parameters = $event->getArgument('parameters');
            if (!array_key_exists('product', $parameters)) {
                throw new \InvalidArgumentException();
            }
        } catch (\InvalidArgumentException $e) {
            return;
        }

        $proposition = $this->manager->findOrCreate($parameters['product']);

        $parameters['proposition'] = $proposition;

        $event->setArgument('parameters', $parameters);
    }
}
