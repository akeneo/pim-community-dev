<?php

namespace Pim\Bundle\ConfigBundle\Form\Subscriber;

use Symfony\Component\Form\FormFactoryInterface;

use Pim\Bundle\ConfigBundle\Helper\LocaleHelper;

use Pim\Bundle\ConfigBundle\Manager\LocaleManager;

use Symfony\Component\Form\FormEvent;

use Symfony\Component\Form\FormEvents;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LocaleFallbackSubscriber implements EventSubscriberInterface
{
    protected $formFactory;

    protected $localeManager;

    protected $localeHelper;

    public function __construct(
        FormFactoryInterface $formFactory,
        LocaleManager $localeManager,
        LocaleHelper $localeHelper
    ) {
        $this->formFactory = $formFactory;
        $this->localeManager = $localeManager;
        $this->localeHelper = $localeHelper;
    }

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

        $this->addFallbackField($form, $data);
    }

    protected function addFallbackField($form, $data)
    {
        // select activated locales without fallbacks
        $choices = $this->localeManager->getFallbackCodes();
        $fallbackChoices = array_combine($choices, $choices);

        $fallbackDisabled = in_array($data->getCode(), $fallbackChoices);

        $placeholder = $fallbackDisabled ? 'Not available' : 'Choose a locale';

        foreach ($fallbackChoices as $key => $value) {
            $fallbackChoices[$key] = $this->localeHelper->getLocalizedLabel($value);
        }

        $options = array(
            'choices' => $fallbackChoices,
            'required' => false,
            'disabled'          => $fallbackDisabled,
            'read_only'         => $fallbackDisabled,
            'label'            => 'Inherited locale',
            'attr'        => array('data-placeholder' => $placeholder),
            'auto_initialize' => false
        );

        $form->add($this->formFactory->createNamed('fallback', 'choice', null, $options));
    }
}
