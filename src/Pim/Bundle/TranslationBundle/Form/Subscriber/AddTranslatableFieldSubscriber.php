<?php

namespace Pim\Bundle\TranslationBundle\Form\Subscriber;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Doctrine\Common\Inflector\Inflector;
use Pim\Bundle\TranslationBundle\Entity\AbstractTranslatableEntity;
use Pim\Bundle\TranslationBundle\Exception\MissingOptionException;
use Pim\Bundle\TranslationBundle\Factory\TranslationFactory;

/**
 * Define subscriber for translation fields
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class AddTranslatableFieldSubscriber implements EventSubscriberInterface
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var TranslationFactory
     */
    protected $translationFactory;

    /**
     * @var multitype:mixed
     */
    protected $options;

    /**
     * Constructor
     *
     * @param FormFactoryInterface $formFactory
     * @param ValidatorInterface   $validator
     * @param multitype:mixed      $options
     */
    public function __construct(FormFactoryInterface $formFactory, ValidatorInterface $validator, array $options)
    {
        $this->formFactory        = $formFactory;
        $this->validator          = $validator;
        $this->options            = $options;

        $this->translationFactory = new TranslationFactory(
            $this->getOption('translation_class'),
            $this->getOption('entity_class'),
            $this->getOption('field')
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::POST_BIND    => 'postBind',
            FormEvents::BIND         => 'bind'
        );
    }

    /**
     * On pre set data event
     * Build the custom form based on the provided locales
     *
     * @param FormEvent $event
     *
     * @return
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data) {
            return;
        }

        $entity = $form->getParent()->getData();
        $entity->setLocale($this->getOption('default_locale'));

        $translations = $this->bindTranslations($data);
        foreach ($translations as $binded) {
            $method = 'get'.Inflector::camelize($this->getOption('field'));
            $content = $binded['translation']->$method();
            $form->add(
                $this->formFactory->createNamed(
                    $binded['fieldName'],
                    $this->getOption('widget'),
                    $content !== null ? $content : '',
                    array(
                        'label'           => $binded['locale'],
                        'required'        => in_array($binded['locale'], $this->getOption('required_locale')),
                        'mapped'          => false,
                        'auto_initialize' => false
                    )
                )
            );
            if ($this->getOption('only_default')) {
                return;
            }
        }
    }

    /**
     * On bind event (validation)
     *
     * @param FormEvent $event
     */
    public function bind(FormEvent $event)
    {
        $form = $event->getForm();

        foreach ($this->getFieldNames() as $locale => $fieldName) {
            $content = $form->get($fieldName)->getData();

            if (null === $content && in_array($locale, $this->getOption('required_locale'))) {
                $form->addError(
                    new FormError(
                        sprintf('Field "%s" for locale "%s" cannot be blank', $this->getOption('field'), $locale)
                    )
                );
            }

            $translation = $this->translationFactory->createTranslation($locale);
            $method = 'set'.Inflector::camelize($this->getOption('field'));
            $translation->$method($content);

            $errors = $this->validator->validate(
                $translation,
                array($locale)
            );

            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            }
        }
    }

    /**
     * On post bind event (after validation)
     *
     * @param FormEvent $event
     */
    public function postBind(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $entity = $form->getParent()->getData();
        $entity->setLocale($this->getOption('default_locale'));

        $translations = $this->bindTranslations($data);
        foreach ($translations as $binded) {
            $content = $form->get($binded['fieldName'])->getData();
            $translation = $binded['translation'];

            if ($content !== null) {
                $method = 'set'.Inflector::camelize($this->getOption('field'));
                $translation->$method($content);
                $translation->setForeignKey($entity);
                $entity->addTranslation($translation);

            } else {
                $entity->removeTranslation($translation);
            }
        }
    }

    /**
     * Small helper to extract all personnal translation from the entity for the field we are interested in
     * and combines it with the fields
     *
     * @param multitype:mixed $data
     *
     * @return mixed string
     */
    protected function bindTranslations($data)
    {
        $collection = array();
        $availableTranslations = array();

        foreach ($data as $translation) {
            $availableTranslations[strtolower($translation->getLocale())] = $translation;
        }

        foreach ($this->getFieldNames() as $locale => $fieldName) {
            if (isset($availableTranslations[strtolower($locale)])) {
                $translation = $availableTranslations[strtolower($locale)];
            } else {
                $translation = $this->translationFactory->createTranslation($locale);
            }

            $collection[] = array(
                'locale'      => $locale,
                'fieldName'   => $fieldName,
                'translation' => $translation,
            );
        }

        return $collection;
    }

    /**
     * Helper method to generate field names in format : '<locale>' => '<field>|<locale>'
     *
     * @return multitype:string
     */
    protected function getFieldNames()
    {
        $collection = array();

        if ($this->getOption('only_default')) {
            $defaultLocale = $this->getOption('default_locale');
            $collection[$defaultLocale] = $defaultLocale;
        } else {
            foreach ($this->getOption('locales') as $locale) {
                $collection[$locale] = $locale;
            }
        }

        return $collection;
    }

    /**
     * Get an option value
     *
     * @param string $name
     *
     * @return mixed
     *
     * @throws MissingOptionException
     */
    protected function getOption($name)
    {
        if (!isset($this->options[$name])) {
            throw new MissingOptionException(sprintf('Option %s is missing', $name));
        }

        return $this->options[$name];
    }
}
