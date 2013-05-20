<?php

namespace Pim\Bundle\TranslationBundle\Form\Subscriber;

use Symfony\Component\Form\Event\DataEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Pim\Bundle\TranslationBundle\Entity\AbstractTranslatableEntity;
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
    private $validator;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var TranslationFactory
     */
    private $translationFactory;

    /**
     * @var string
     */
    private $field;

    /**
     * @var string
     */
    private $widget;

    /**
     * @var string
     */
    private $requiredLocale;

    /**
     * @var array
     */
    private $locales;

    /**
     * Constructor
     *
     * @param FormFactoryInterface $formFactory
     * @param ValidatorInterface   $validator Validator
     * @param TranslationFactory   $translationFactory
     * @param string               $field
     * @param string               $widget
     * @param string               $requiredLocale
     * @param array                $locales
     */
    public function __construct(FormFactoryInterface $formFactory, ValidatorInterface $validator, TranslationFactory $translationFactory, $field, $widget, $requiredLocale, array $locales)
    {
        $this->formFactory        = $formFactory;
        $this->validator          = $validator;
        $this->translationFactory = $translationFactory;
        $this->field              = $field;
        $this->widget             = $widget;
        $this->requiredLocale     = array($requiredLocale);
        $this->locales            = $locales;
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
     * @param DataEvent $event
     */
    public function preSetData(DataEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data) {
            return;
        }

        $entity = $form->getParent()->getData();
        $entity->setTranslatableLocale('default');

        $translations = $this->bindTranslations($data);
        foreach ($translations as $binded) {
            $form->add(
                $this->formFactory->createNamed(
                    $binded['fieldName'],
                    $this->widget,
                    $binded['translation']->getContent() !== null ? $binded['translation']->getContent() : '',
                    array(
                        'label'         => $binded['locale'],
                        'required'      => in_array($binded['locale'], $this->requiredLocale),
                        'property_path' => false,
                    )
                )
            );
        }
    }

    /**
     * On bind event (validation)
     *
     * @param DataEvent $event
     */
    public function bind(DataEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        foreach ($this->getFieldNames() as $locale => $fieldName) {
            $content = $form->get($fieldName)->getData();

            if (null === $content && in_array($locale, $this->requiredLocale)) {
                $form->addError(new FormError(sprintf(
                    'Field "%s" for locale "%s" cannot be blank', $this->field, $locale
                )));
            }

            $translation = $this->translationFactory->createTranslation($locale);
            $translation->setContent($content);

            $errors = $this->validator->validate($translation, array(sprintf("%s:%s", $this->field, $locale)));

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
     * @param DataEvent $event
     */
    public function postBind(DataEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $entity = $form->getParent()->getData();
        $entity->setTranslatableLocale('default');

        $translations = $this->bindTranslations($data);
        foreach ($translations as $binded) {
            $content = $form->get($binded['fieldName'])->getData();
            $translation = $binded['translation'];

            if ($content !== null) {
                // set the submitted content
                $translation->setContent($content);
                $translation->setForeignKey($entity);

                if ($translation->getLocale() === 'default') {
                    $methodName = 'set'. ucfirst($this->field);
                    $entity->$methodName($translation->getContent());
                }
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
    private function bindTranslations($data)
    {
        $collection = array();
        $availableTranslations = array();

        foreach ($data as $translation) {
            if (strtolower($translation->getField()) === strtolower($this->field)) {
                $availableTranslations[strtolower($translation->getLocale())] = $translation;
            }
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
    private function getFieldNames()
    {
        $collection = array();

        foreach ($this->locales as $locale) {
            $collection[$locale] = sprintf('%s:%s', $this->field, $locale);
        }

        return $collection;
    }
}
