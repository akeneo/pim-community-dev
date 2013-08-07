<?php

namespace Pim\Bundle\ConfigBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

/**
 * Locale fallback subscriber
 *
 * @TODO : Explain the purpose of this class
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleSubscriber implements EventSubscriberInterface
{
    /**
     * Form factory
     * @var FormFactoryInterface
     */
    protected $factory;

    /**
     * All locales
     * @var array $locales
     */
    protected $locales;

    /**
     * Existing locales
     * @var array $existingLocales
     */
    protected $existingLocales;

    /**
     * Locales with a fallback
     * @var array $localesWithFallback
     */
    protected $localesWithFallback;

    /**
     * Constructor
     *
     * @param FormFactoryInterface $factory             Form factory
     * @param array                $locales             All locales
     * @param array                $existingLocales     Existing locales
     * @param array                $localesWithFallback Locales with a fallback
     */
    public function __construct(
        FormFactoryInterface $factory = null,
        $locales = array(),
        $existingLocales = array(),
        $localesWithFallback = array()
    ) {
        $this->factory = $factory;
        $this->locales = $locales;
        $this->existingLocales = $existingLocales;
        $this->localesWithFallback = $localesWithFallback;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
        );
    }

    /**
     * Method called before set data
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data) {
            return;
        }

        $this->addLocaleField($form, $data);
        $this->addFallbackField($form, $data);
    }

    /**
     * Add locale field
     * @param Form   $form
     * @param Locale $data
     */
    protected function addLocaleField($form, $data)
    {
        $choices = $this->locales;

        foreach ($this->existingLocales as $locale) {
            if ($locale !== $data->getCode()) {
                unset($choices[$locale]);
            }
        }

        $options = array(
            'choices'           => $choices,
            'required'          => true,
            'disabled'          => $data->getId(),
            'read_only'         => $data->getId(),
            // TODO : use locale manager for preferred choices ? get all locales activated as preferred choices
            'preferred_choices' => array('fr_FR', 'en_US'),
            'label'             => 'Locale',
            'auto_initialize' => false
        );

        $form->add($this->factory->createNamed('code', 'choice', null, $options));
    }

    /**
     * Add fallback field
     * @param Form   $form
     * @param Locale $data
     *
     * @return void
     */
    protected function addFallbackField($form, $data)
    {
        $choices = $this->locales;

        foreach (array_keys($choices) as $code) {
            if (!in_array($code, $this->existingLocales)) {
                unset($choices[$code]);
            }
        }

        foreach ($this->localesWithFallback as $locale) {
            unset($choices[$locale->getCode()]);
        }

        $fallbackLocales = array_map(
            function ($locale) {
                return $locale->getFallback();
            },
            $this->localesWithFallback
        );

        $fallbackDisabled = in_array($data->getCode(), $fallbackLocales);

        $placeholder = $fallbackDisabled ? 'Not available' : 'Choose a locale';

        $options = array(
            'choices'           => $choices,
            'required'          => false,
            'disabled'          => $fallbackDisabled,
            'read_only'         => $fallbackDisabled,
            // TODO : use locale manager for preferred choices ? get all locales activated as preferred choices
            'preferred_choices' => array('fr_FR', 'en_US'),
            'label'             => 'Inherited locale',
            'attr'              => array('data-placeholder' => $placeholder),
            'auto_initialize' => false
        );

        $form->add($this->factory->createNamed('fallback', 'choice', null, $options));
    }
}
