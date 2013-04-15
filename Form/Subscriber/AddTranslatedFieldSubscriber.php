<?php
namespace Pim\Bundle\TranslationBundle\Form\Subscriber;

use Symfony\Component\Form\Event\DataEvent;

use Symfony\Component\Form\FormEvents;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\Form\FormFactoryInterface;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 *
 * Enter description here ...
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class AddTranslatedFieldSubscriber implements EventSubscriberInterface
{
    private $factory;
    private $options;
    private $container;

    /**
     *
     * @param FormFactoryInterface $factory
     * @param ContainerInterface $container
     * @param array $options
     */
    public function __construct(FormFactoryInterface $factory, ContainerInterface $container, Array $options)
    {
        $this->factory = $factory;
        $this->options = $options;
        $this->container = $container;
    }

    /**
     *
     * @return multitype:string
     */
    public static function getSubscribedEvents()
    {
        // Tells the dispatcher that we want to listen on the form.pre_set_data
        // , form.post_data and form.bind_norm_data event
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::POST_BIND => 'postBind',
            FormEvents::BIND => 'bind'
        );
    }

    /**
     *
     * @param unknown_type $data
     * @return multitype:multitype:unknown string
     */
    private function bindTranslations($data)
    {
//         var_dump('______________bind translations_______________');

        //Small helper function to extract all Personal Translation
        //from the Entity for the field we are interested in
        //and combines it with the fields

        $collection = array();
        $availableTranslations = array();

        foreach($data as $translation)
        {
            if(strtolower($translation->getField()) == strtolower($this->options['field']))
            {
                $availableTranslations[strtolower($translation->getLocale())] = $translation;
            }
        }

        foreach($this->getFieldNames() as $locale => $fieldName)
        {
            if (isset($availableTranslations[strtolower($locale)])) {
                $translation = $availableTranslations[strtolower($locale)];
            } else {
                $translation = $this->createPersonalTranslation($locale, $this->options['field'], null);
            }

//             if ($locale != 'default') {
                $collection[] = array(
                    'locale'      => $locale,
                    'fieldName'   => $fieldName,
                    'translation' => $translation,
                );
//             } else {
//                 $collection['default'] = $this->options['field'];
//             }

        }

//         var_dump('______________________________________________');

        return $collection;
    }

    /**
     *
     * @return multitype:string
     */
    private function getFieldNames()
    {
//         var_dump('______________get field names_______________');
        //helper function to generate all field names in format:
        // '<locale>' => '<field>|<locale>'
        $collection = array();

        foreach ($this->options['locales'] as $locale) {
            $collection[$locale] = $this->options['field'] .":". $locale;
        }

//         var_dump('____________________________________________');

        return $collection;
    }

    /**
     *
     * @param unknown_type $locale
     * @param unknown_type $field
     * @param unknown_type $content
     * @return unknown
     */
    private function createPersonalTranslation($locale, $field, $content)
    {
//         var_dump('_______________create personal translation_________________');

        //creates a new Personal Translation
        $className = $this->options['personal_translation'];

        $translation = new $className($locale, $field, $content);
        $translation->setObjectClass('Pim\Bundle\ProductBundle\Entity\AttributeGroup');
        $translation->setLocale($locale);
        $translation->setField($field);
        $translation->setContent($content);
//         $translation->setForeignKey();
//         $translation->setLocale($locale);
//         $translation->setField($field);
//         $translation->setContent($content);
//         var_dump('___________________________________________________________');

        return $translation;
    }

    /**
     *
     * @param DataEvent $event
     */
    public function bind(DataEvent $event)
    {
//         var_dump('____________________bind norm data__________________');

        //Validates the submitted form
        $data = $event->getData();
        $form = $event->getForm();

        $entity = $form->getParent()->getData();
        $entity->setTranslatableLocale('default');

        $validator = $this->container->get('validator');

        foreach ($this->getFieldNames() as $locale => $fieldName)
        {
//             var_dump('call content');
//             if ($locale != 'default') {
                $content = $form->get($fieldName)->getData();
//             var_dump('end call content');

                if (null === $content && in_array($locale, $this->options['required_locale'])) {
                    $form->addError(new FormError(sprintf("Field '%s' for locale '%s' cannot be blank", $this->options['field'], $locale)));
                } else {
                    $translation = $this->createPersonalTranslation($locale, $fieldName, $content);
//                     $translation->setContent($content);
                    $errors = $validator->validate($translation, array(sprintf("%s:%s", $this->options['field'], $locale)));

                    if (count($errors) > 0) {
                        foreach ($errors as $error) {
                            $form->addError(new FormError($error->getMessage()));
                        }
                    }
                }
//             }
        }

//         var_dump('____________________________________________________');
    }

    /**
     *
     * @param DataEvent $event
     */
    public function postBind(DataEvent $event)
    {
//         var_dump('________________post bind__________________');

        //if the form passed the validattion then set the corresponding Personal Translations
        $form = $event->getForm();
        $data = $form->getData();

        $entity = $form->getParent()->getData();
        $entity->setTranslatableLocale('default');


//         var_dump(get_class($entity));

        $translations = $this->bindTranslations($data);
//         var_dump($translations);
//         $translations = $collection['translations'];
//         $default = $collection['default'];

        foreach ($translations as $binded) {
            $content = $form->get($binded['fieldName'])->getData();
            $translation = $binded['translation'];
//             var_dump($translation);
//             var_dump('------------------------------------------');

            // set the submitted content
            $translation->setContent($content);
            $translation->setForeignKey($entity);

            //test if its new
            if ($translation->getId()) {
                //Delete the Personal Translation if its empty
                if (NULL === $content && $this->options['remove_empty']) {
                    $data->removeElement($translation);

//                     if ($this->options['entity_manager_removal']) {
//                         $this->container->get('doctrine.orm.entity_manager')->remove($translation);
//                     }
                }
            }
            elseif (NULL !== $content) {
                if ($translation->getLocale() === 'default') {
                    $entity->setName($translation->getContent());
//                     $entity->addTranslation($translation);
//                     $entity->removeTranslation($translation);

//                     if ($data->contains($translation)) {
//                         $data->remove($translation);
//                     }
                }

                    $entity->addTranslation($translation);
                    if (!$data->contains($translation)) {
                        $data->add($translation);
                    }

//                     $entity->setName($content);
//                     $data->removeElement($translation);
//                 } else {
                    //add it to entity

//                 }
            }
        }
    }

    /**
     *
     * @param DataEvent $event
     */
    public function preSetData(DataEvent $event)
    {
        //Builds the custom 'form' based on the provided locales
        $data = $event->getData();
        $form = $event->getForm();

        // During form creation setData() is called with null as an argument
        // by the FormBuilder constructor. We're only concerned with when
        // setData is called with an actual Entity object in it (whether new,
        // or fetched with Doctrine). This if statement let's us skip right
        // over the null condition.
        if (null === $data) {
            return;
        }

        $entity = $form->getParent()->getData();
        $entity->setTranslatableLocale('default');

        $translations = $this->bindTranslations($data);

        foreach ($translations as $binded) {
            $content = ($binded['translation']->getContent() !== null) ? $binded['translation']->getContent() : $entity->getName();

            $form->add($this->factory->createNamed(
                $binded['fieldName'],
                $this->options['widget'],
                $content,
                array(
                    'label' => $binded['locale'],
                    'required' => in_array($binded['locale'], $this->options['required_locale']),
                    'property_path'=> false,
                )
            ));
        }
    }
}
