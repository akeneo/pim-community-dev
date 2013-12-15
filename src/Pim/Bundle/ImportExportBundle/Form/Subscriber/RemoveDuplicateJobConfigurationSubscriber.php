<?php

namespace Pim\Bundle\ImportExportBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Form;
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
     * Remove duplicate configuration fields from the form
     * Data will be passsed to the steps on the 'submit' event
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

        $existingFields = array();
        foreach ($steps->all() as $stepForm) {
            foreach ($stepForm->all() as $stepElementForm) {
                $this->removeDuplicateFields($stepElementForm, $existingFields);
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

        $form = $event->getForm()->get('steps');

        $configuration = $this->extractJobConfiguration($form);
        $job->setConfiguration($configuration);
    }

    /**
     * Remove duplicate fields from the form
     *
     * @param Form  $form
     * @param array &$existingFields
     */
    protected function removeDuplicateFields(Form $form, array &$existingFields)
    {
        foreach (array_keys($form->all()) as $field) {
            if (in_array($field, $existingFields)) {
                $form->remove($field);
            } else {
                $existingFields[] = $field;
            }
        }
    }

    /**
     * Extract the updated job configuration from the job steps form
     *
     * @param Form $form
     *
     * @return array
     */
    protected function extractJobConfiguration(Form $form)
    {
        $configuration = array();

        foreach ($form->all() as $stepForm) {
            foreach ($stepForm->all() as $stepElementForm) {
                foreach ($stepElementForm->all() as $element => $valueForm) {
                    $configuration[$element] = $valueForm->getData();
                }
            }
        }

        return $configuration;
    }
}
