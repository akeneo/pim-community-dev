<?php

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Enrich;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Pim\Bundle\EnrichBundle\EnrichEvents;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PropositionManager;

/**
 * Add the proposition form view to the product edit template parameters
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AddPropositionFormViewParameterSubscriber implements EventSubscriberInterface
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var PropositionManager */
    protected $manager;

    public function __construct(
        FormFactoryInterface $formFactory,
        PropositionManager $manager
    ) {
        $this->formFactory = $formFactory;
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            EnrichEvents::PRE_RENDER_PRODUCT_EDIT => 'addPropositionFormView',
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
            if (!array_key_exists('product', $parameters) || !array_key_exists('dataLocale', $parameters)) {
                throw new \InvalidArgumentException();
            }
        } catch (\InvalidArgumentException $e) {
            return;
        }

        $proposition = $this->manager->findOrCreate(
            $parameters['product'],
            $parameters['dataLocale']
        );

        $parameters['propositionForm'] = $this
            ->formFactory
            ->create('pimee_workflow_proposition', $proposition)
            ->createView();

        $event->setArgument('parameters', $parameters);
    }
}
