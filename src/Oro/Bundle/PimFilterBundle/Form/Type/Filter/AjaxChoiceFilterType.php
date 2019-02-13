<?php

namespace Oro\Bundle\PimFilterBundle\Form\Type\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;
use Oro\Bundle\PimFilterBundle\Form\Type\UnstructuredType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for ajax choice filter
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AjaxChoiceFilterType extends ChoiceFilterType
{
    /** @staticvar string */
    const NAME = 'pim_type_ajax_choice_filter';

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceFilterType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('type', $options['operator_type'], ['choices' => $this->getOperatorChoices($options)]);
        $builder->add('value', UnstructuredType::class);
        $builder->add('valueChoices', ChoiceType::class, $options['field_options'] + ['mapped' => false]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(
            [
                'choices'           => [],
                'preload_choices'   => false,
                'choice_url'        => null,
                'choice_url_params' => null,
                'field_options'     => []
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['choices'] = $view->children['valueChoices']->vars['choices'];
        $view->vars['preload_choices'] = $options['preload_choices'];
        $view->vars['choice_url'] = $options['choice_url'];
        $view->vars['choice_url_params'] = $options['choice_url_params'];
        $view->vars['empty_choice'] = isset($options['field_options']['attr']['empty_choice']) ?
            $options['field_options']['attr']['empty_choice'] :
            false;
    }

    /**
     * Returns the available operator choices
     *
     * @param array $options
     *
     * @return array
     */
    protected function getOperatorChoices($options)
    {
        $operatorChoices = [strtolower(Operators::IN_LIST)];

        if (isset($options['field_options']['attr']['empty_choice']) &&
            true === $options['field_options']['attr']['empty_choice']) {
            $operatorChoices[] = strtolower(Operators::IS_EMPTY);
            $operatorChoices[] = strtolower(Operators::IS_NOT_EMPTY);
        }

        return array_combine($operatorChoices, $operatorChoices);
    }
}
