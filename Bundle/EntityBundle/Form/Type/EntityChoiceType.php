<?php

namespace Oro\Bundle\EntityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\FormBundle\Form\Type\ChoiceListItem;

class EntityChoiceType extends AbstractType
{
    const NAME = 'oro_entity_choice';

    /**
     * @var EntityProvider
     */
    protected $provider;

    /**
     * Constructor
     *
     * @param EntityProvider $provider
     */
    public function __construct(EntityProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $that    = $this;
        $choices = function (Options $options) use ($that) {
            return $that->getChoices($options['show_plural']);
        };

        $resolver->setDefaults(
            array(
                'choices'     => $choices,
                'empty_value' => '',
                'show_plural' => true,
                'configs'     => array(
                    'is_translate_option'     => false,
                    'placeholder'             => 'oro.entity.form.choose_entity',
                    'result_template_twig'    => 'OroEntityBundle:Choice:entity/result.html.twig',
                    'selection_template_twig' => 'OroEntityBundle:Choice:entity/selection.html.twig',
                )
            )
        );
    }

    /**
     * Returns a list of choices
     *
     * @param bool $showPlural If true a plural label will be used as a choice text; otherwise, a label will be used
     * @return array of entities
     *               key = full class name, value = ChoiceListItem
     */
    protected function getChoices($showPlural)
    {
        $choices = array();

        $entities = $this->provider->getEntities($showPlural);
        foreach ($entities as $entity) {
            $attributes = [];
            foreach ($entity as $key => $val) {
                if (!in_array($key, ['name'])) {
                    $attributes['data-' . str_replace('_', '-', $key)] = $val;
                }
            }
            $choices[$entity['name']] = new ChoiceListItem(
                $showPlural ? $entity['plural_label'] : $entity['label'],
                $attributes
            );
        }

        return $choices;
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
