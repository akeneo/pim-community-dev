<?php

namespace Pim\Bundle\FilterBundle\Form\Type\Filter;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;
use Oro\Bundle\MeasureBundle\Manager\MeasureManager;

/**
 * Metric filter type for products
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricFilterType extends NumberFilterType
{
    /**
     * @staticvar string
     */
    const NAME = 'pim_type_metric_filter';

    const TYPE_GREATER_EQUAL = 1;
    const TYPE_GREATER_THAN = 2;
    const TYPE_EQUAL = 3;
    const TYPE_LESS_EQUAL = 4;
    const TYPE_LESS_THAN = 5;

    const DATA_INTEGER = 'data_integer';
    const DATA_DECIMAL = 'data_decimal';

    /**
     * Constructor
     *
     * @param TranslatorInterface $translator
     * @param MeasureManager      $measureManager
     */
    public function __construct(TranslatorInterface $translator, MeasureManager $measureManager)
    {
        parent::__construct($translator);

        $this->measureManager = $measureManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('unit', 'choice', $this->createUnitOptions($options));

        $builder->add('operator', 'choice', array('choices' => $this->getOperatorChoices()));

        $builder->add('value', 'number');
    }

    /**
     * Get operator choices
     *
     * @return array
     */
    protected function getOperatorChoices()
    {
        return array(
            self::TYPE_EQUAL => $this->translator->trans('label_type_equal', array(), 'OroFilterBundle'),
            self::TYPE_GREATER_EQUAL =>
                $this->translator->trans('label_type_greater_equal', array(), 'OroFilterBundle'),
            self::TYPE_GREATER_THAN => $this->translator->trans('label_type_greater_than', array(), 'OroFilterBundle'),
            self::TYPE_LESS_EQUAL => $this->translator->trans('label_type_less_equal', array(), 'OroFilterBundle'),
            self::TYPE_LESS_THAN => $this->translator->trans('label_type_less_than', array(), 'OroFilterBundle'),
        );
    }

    /**
     * Create unit symbols options list
     *
     * @param array $options
     *
     * @throws FormException
     *
     * @return array
     */
    protected function createUnitOptions(array $options)
    {
        $result = array('required' => true);

        $family = $options['field_options']['family'];
        $result['choices'] = $this->measureManager->getUnitSymbolsForFamily($family);

        return $result;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_type' => 'data_decimal',
                'operator_choices' => $this->getOperatorChoices(),
                'field_options' => array(),
                'formatter_options' => array()
            )
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars['unit']['type'] = $this->measureManager->getUnitSymbolsForFamily($options['field_options']['family']);
        $view->vars['value']['type'] = 'number';
        $view->vars['operator']['type'] = $this->getOperatorChoices();
    }
}
