<?php

namespace Akeneo\Platform\Bundle\UIBundle\Form\Subscriber;

use Akeneo\Platform\Bundle\UIBundle\Exception\MissingOptionException;
use Akeneo\Tool\Component\Localization\Factory\TranslationFactory;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Define subscriber for translation fields
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * @var array
     */
    protected $options;

    /**
     * @var UserContext
     */
    protected $userContext;

    /**
     * Constructor
     *
     * @param FormFactoryInterface $formFactory
     * @param ValidatorInterface   $validator
     * @param UserContext          $userContext
     * @param array                $options
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        UserContext $userContext,
        array $options
    ) {
        $this->formFactory = $formFactory;
        $this->validator = $validator;
        $this->userContext = $userContext;
        $this->options = $options;

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
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::POST_SUBMIT  => 'postSubmit',
            FormEvents::SUBMIT       => 'submit'
        ];
    }

    /**
     * On pre set data event
     * Build the custom form based on the provided locales
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data) {
            return;
        }
        $translations = $this->bindTranslations($data);
        foreach ($translations as $binded) {
            $method = 'get'.Inflector::camelize($this->getOption('field'));
            $content = $binded['translation']->$method();
            $form->add(
                $this->formFactory->createNamed(
                    $binded['fieldName'],
                    $this->getOption('widget'),
                    $content !== null ? $content : '',
                    [
                        'label'           => $this->getLocaleLabel($binded['locale']),
                        'required'        => in_array($binded['locale'], $this->getOption('required_locale')),
                        'mapped'          => false,
                        'auto_initialize' => false
                    ]
                )
            );
        }
    }

    /**
     * On submit event (validation)
     *
     * @param FormEvent $event
     */
    public function submit(FormEvent $event)
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
                $translation
            );

            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            }
        }
    }

    /**
     * On post submit event (after validation)
     *
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $entity = $form->getParent()->getData();

        $translations = $this->bindTranslations($data);

        foreach ($translations as $binded) {
            $content = $form->get($binded['fieldName'])->getData();
            $translation = $binded['translation'];

            $method = 'set'.Inflector::camelize($this->getOption('field'));
            $translation->$method($content);
            $translation->setForeignKey($entity);
            $entity->addTranslation($translation);
        }
    }

    /**
     * Small helper to extract all personnal translation from the entity for the field we are interested in
     * and combines it with the fields
     *
     * @param array $data
     *
     * @return mixed string
     */
    protected function bindTranslations($data)
    {
        $collection = [];
        $availableTrans = [];

        foreach ($data as $translation) {
            $availableTrans[strtolower($translation->getLocale())] = $translation;
        }

        foreach ($this->getFieldNames() as $locale => $fieldName) {
            if (isset($availableTrans[strtolower($locale)])) {
                $translation = $availableTrans[strtolower($locale)];
            } else {
                $translation = $this->translationFactory->createTranslation($locale);
            }

            $collection[] = [
                'locale'      => $locale,
                'fieldName'   => $fieldName,
                'translation' => $translation,
            ];
        }

        return $collection;
    }

    /**
     * Helper method to generate field names in format : '<locale>' => '<field>|<locale>'
     *
     * @return string[]
     */
    protected function getFieldNames()
    {
        $userLocales = $this->userContext->getUserLocaleCodes();
        $collection = [];

        foreach ($this->getOption('locales') as $locale) {
            if (in_array($locale, $userLocales)) {
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
     * @throws MissingOptionException
     *
     * @return mixed
     */
    protected function getOption($name)
    {
        if (!isset($this->options[$name])) {
            throw new MissingOptionException(sprintf('Option %s is missing', $name));
        }

        return $this->options[$name];
    }

    /**
     * Returns the label of a locale in the specified language
     *
     * @param string $code        the code of the locale to translate
     * @param string $translateIn the locale in which the label should be translated (if null, user locale will be used)
     *
     * @return string
     */
    private function getLocaleLabel($code, $translateIn = null)
    {
        $translateIn = $translateIn ?: $this->userContext->getCurrentLocaleCode();

        return \Locale::getDisplayName($code, $translateIn);
    }
}
