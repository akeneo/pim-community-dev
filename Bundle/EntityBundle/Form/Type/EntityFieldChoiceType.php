<?php

namespace Oro\Bundle\EntityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
use Oro\Bundle\EntityBundle\Manager\EntityFieldManager;
use Oro\Bundle\FormBundle\Form\Type\ChoiceListItem;

class EntityFieldChoiceType extends AbstractType
{
    const NAME = 'oro_entity_field_choice';

    /**
     * @var EntityFieldManager
     */
    protected $manager;

    /**
     * Constructor
     *
     * @param EntityFieldManager $manager
     */
    public function __construct(EntityFieldManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $that    = $this;
        $choices = function (Options $options) use ($that) {
            return empty($options['entity'])
                ? array() // return empty list if entity is not specified
                : $that->getChoices($options['entity'], $options['with_relations']);
        };

        $resolver->setDefaults(
            array(
                'entity'         => null,
                'with_relations' => false,
                'choices'        => $choices,
                'empty_value'    => '',
                'configs'        => array(
                    'is_translate_option'     => false,
                    'placeholder'             => 'oro.entity.form.choose_entity_field',
                    'result_template_twig'    => 'OroEntityBundle:Choice:entity_field/result.html.twig',
                    'selection_template_twig' => 'OroEntityBundle:Choice:entity_field/selection.html.twig',
                )
            )
        );
    }

    /**
     * Returns a list of choices
     *
     * @param string $entityName    Entity name. Can be full class name or short form: Bundle:Entity.
     * @param bool   $withRelations Indicates whether fields of related entities should be returned as well.
     * @return array
     */
    protected function getChoices($entityName, $withRelations)
    {
        $choices = array();
        $items   = $this->manager->getFields($entityName, $withRelations);
        foreach ($items as $entity) {
            foreach ($entity['fields'] as $field) {
                $key           = $this->getChoiceKey($entity['name'], $field['name'], $withRelations);
                $choices[$key] = $field['label'];
            }
        }

        return $choices;
    }

    /**
     * @param string $className
     * @param string $fieldName
     * @param bool   $withRelations
     * @return string
     */
    protected function getChoiceKey($className, $fieldName, $withRelations)
    {
        return $withRelations
            ? sprintf('%s::%s', $className, $fieldName)
            : $fieldName;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'genemu_jqueryselect2_choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
