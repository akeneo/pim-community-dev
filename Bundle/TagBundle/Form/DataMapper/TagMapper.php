<?php

namespace Oro\Bundle\TagBundle\Form\DataMapper;

use Oro\Bundle\TagBundle\Entity\TagManager;

use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class TagMapper implements DataMapperInterface
{
    /**
     * @var TagManager
     */
    protected $manager;

    public function __construct(TagManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function mapDataToForms($data, $forms)
    {
        if (null === $data) {
            return;
        }

        if (!is_array($data) && !is_object($data)) {
            throw new UnexpectedTypeException($data, 'object, array or empty');
        }

        $tags = $this->manager->prepareArray($data);
        $ownTags = array_filter(
            $tags,
            function ($item) {
                return isset($item['owner']) && $item['owner'];
            }
        );

        foreach ($forms as $field) {
            $name = $field->getConfig()->getName();

            switch($name) {
                case 'all':
                    $field->setData($tags);
                    break;
                case 'own':
                    $field->setData($ownTags);
                    break;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function mapFormsToData($forms, &$data)
    {
        $a =1;

        if (null === $data) {
            return;
        }

        if (!is_array($data) && !is_object($data)) {
            throw new UnexpectedTypeException($data, 'object, array or empty');
        }

//        $newData = new ArrayCollection();
//
//        foreach ($forms as $translationsFieldsForm) {
//            $locale = $translationsFieldsForm->getConfig()->getName();
//
//            foreach ($translationsFieldsForm->getData() as $field => $content) {
//                $existingTranslation = $data ? $data->filter(function ($object) use ($locale, $field) {
//                    return ($object && ($object->getLocale() === $locale) && ($object->getField() === $field));
//                })->first() : null;
//
//                if ($existingTranslation) {
//                    $existingTranslation->setContent($content);
//                    $newData->add($existingTranslation);
//
//                } else {
//                    $translation = new $this->translationClass();
//                    $translation->setLocale($locale);
//                    $translation->setField($field);
//                    $translation->setContent($content);
//                    $newData->add($translation);
//                }
//            }
//        }
//
//        $data = $newData;
    }
}
