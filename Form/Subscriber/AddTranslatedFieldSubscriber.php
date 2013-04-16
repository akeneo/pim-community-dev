<?php
namespace Pim\Bundle\TranslationBundle\Form\Subscriber;

use Symfony\Component\Form\Event\DataEvent;

use Symfony\Component\Form\FormEvents;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\Form\FormFactoryInterface;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Define subscriber for translation fields
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class AddTranslatedFieldSubscriber implements EventSubscriberInterface
{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var FormFactoryInterface
     */
    private $factory;

    /**
     * @var array
     */
    private $options;

    /**
     * Constructor
     * @param FormFactoryInterface $factory   Form factory
     * @param ContainerInterface   $container Service container
     * @param array                $options   Option for fields
     */
    public function __construct(FormFactoryInterface $factory, ContainerInterface $container, Array $options)
    {
        $this->factory = $factory;
        $this->container = $container;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::POST_BIND => 'postBind',
            FormEvents::BIND => 'bind'
        );
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
            if (strtolower($translation->getField()) == strtolower($this->options['field'])) {
                $availableTranslations[strtolower($translation->getLocale())] = $translation;
            }
        }

        foreach ($this->getFieldNames() as $locale => $fieldName) {
            if (isset($availableTranslations[strtolower($locale)])) {
                $translation = $availableTranslations[strtolower($locale)];
            } else {
                $translation = $this->createPersonalTranslation($locale);
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

        foreach ($this->options['locales'] as $locale) {
            $collection[$locale] = $this->options['field'] .':'. $locale;
        }

        return $collection;
    }

    /**
     * Create new translation entity
     *
     * @param string $locale
     *
     * @return object
     */
    private function createPersonalTranslation($locale)
    {
        $className = $this->options['translation_class'];

        $translation = new $className();
        $translation->setLocale($locale);
        $translation->setObjectClass($this->options['entity_class']);
        $translation->setField($this->options['field']);

        return $translation;
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

        // get value of default translation
        $entity = $form->getParent()->getData();
        $entity->setTranslatableLocale('default');

        // Add field for each translation
        $translations = $this->bindTranslations($data);
        foreach ($translations as $binded) {
            $methodName = 'get'. ucfirst($this->options['field']);
            $content = ($binded['translation']->getContent() !== null)
                ? $binded['translation']->getContent()
                : $entity->$methodName();

            $form->add(
                $this->factory->createNamed(
                    $binded['fieldName'],
                    $this->options['widget'],
                    $content,
                    array(
                        'label' => $binded['locale'],
                        'required' => in_array($binded['locale'], $this->options['required_locale']),
                        'property_path'=> false,
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

        $validator = $this->container->get('validator');

        foreach ($this->getFieldNames() as $locale => $fieldName) {
            $content = $form->get($fieldName)->getData();

            if (null === $content && in_array($locale, $this->options['required_locale'])) {
                $form->addError(
                    new FormError(
                        sprintf("Field '%s' for locale '%s' cannot be blank", $this->options['field'], $locale)
                    )
                );
            } else {
                $translation = $this->createPersonalTranslation($locale);

                $errors = $validator->validate($translation, array(sprintf("%s:%s", $this->options['field'], $locale)));

                if (count($errors) > 0) {
                    foreach ($errors as $error) {
                        $form->addError(new FormError($error->getMessage()));
                    }
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
        $data = $form->getData();

        $entity = $form->getParent()->getData();
        $entity->setTranslatableLocale('default');

        $translations = $this->bindTranslations($data);

        foreach ($translations as $binded) {
            $content = $form->get($binded['fieldName'])->getData();
            $translation = $binded['translation'];

            // set the submitted content
            $translation->setContent($content);
            $translation->setForeignKey($entity);

            if ($translation->getLocale() === 'default') {
                $methodName = 'set'. ucfirst($this->options['field']);
                $entity->$methodName($translation->getContent());
            }
            $entity->addTranslation($translation);
        }
    }
}
