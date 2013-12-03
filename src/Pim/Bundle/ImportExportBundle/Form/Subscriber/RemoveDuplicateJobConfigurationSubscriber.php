<?php

namespace Pim\Bundle\ImportExportBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Subscriber to remove duplicated job configuration from the form
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveDuplicateJobConfigurationSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_SET_DATA => 'postSetData',
            FormEvents::SUBMIT        => 'submit'
        );
    }

    /**
     * Remove duplicated configuration from the form, it will
     * be provided to the steps on the 'submit' event
     *
     * @param FormEvent $event
     */
    public function postSetData(FormEvent $event)
    {
        $job = $event->getData();
        if (null === $job) {
            return;
        }
        $form = $event->getForm();

        $steps = $form->get('steps');

        $existingConfig = array();
        foreach (array_keys($steps->all()) as $step) {
            $stepForm = $steps->get($step);
            foreach (array_keys($stepForm->all()) as $stepElement) {
                $stepElementForm = $stepForm->get($stepElement);
                foreach (array_keys($stepElementForm->all()) as $stepElementConfig) {
                    if (in_array($stepElementConfig, $existingConfig)) {
                        $stepElementForm->remove($stepElementConfig);
                    } else {
                        $existingConfig[] = $stepElementConfig;
                    }
                }
            }
        }
    }

    /**
     * Synchronize the configuration to all the steps of the job
     * to make it available to steps that duplicate configuration
     *
     * @param FormEvent $event
     */
    public function submit(FormEvent $event)
    {
        $job = $event->getData();
        if (null === $job) {
            return;
        }

        $job->syncConfiguration();
    }
}
