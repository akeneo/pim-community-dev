<?php

namespace Oro\Bundle\EntityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\FormBundle\Form\Type\ChoiceListItem;

class EntityChoiceType extends AbstractType
{
    const NAME = 'oro_entity_choice';

    /**
     * @var ConfigProvider
     */
    protected $entityConfigProvider;

    /**
     * Constructor
     *
     * @param ConfigProvider $entityConfigProvider
     */
    public function __construct(ConfigProvider $entityConfigProvider)
    {
        $this->entityConfigProvider = $entityConfigProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'choices'     => $this->getChoices(),
                'empty_value' => '',
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
     * @return array of entities which can be used to build a report
     *               key = full class name, value = ChoiceListItem
     */
    protected function getChoices()
    {
        $choices = array();

        // get all configurable entities
        $configs = $this->entityConfigProvider->getConfigs();
        foreach ($configs as $config) {
            $choices[$config->getId()->getClassName()] = new ChoiceListItem(
                $config->get('plural_label'),
                array(
                    'data-icon' => $config->get('icon')
                )
            );
        }

        // sort choices
        $this->sortChoices($choices);

        return $choices;
    }

    /**
     * Sorts choices
     *
     * @param array $choices
     */
    protected function sortChoices(array &$choices)
    {
        // sort choices by entity name
        uasort(
            $choices,
            function ($a, $b) {
                return strcmp((string)$a, (string)$b);
            }
        );
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
