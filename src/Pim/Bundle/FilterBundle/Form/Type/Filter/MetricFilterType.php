<?php

namespace Pim\Bundle\FilterBundle\Form\Type\Filter;

use Monolog\Logger;

use Symfony\Component\Form\FormInterface;

use Symfony\Component\Form\FormView;

use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;

use Symfony\Component\Form\AbstractType;

use Oro\Bundle\FormBundle\Form\Exception\FormException;

use Symfony\Component\Form\FormBuilderInterface;

use Oro\Bundle\MeasureBundle\Manager\MeasureManager;

use Symfony\Component\Translation\TranslatorInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;

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
        $this->translator     = $translator;
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
                'show_filter' => true // TODO : must be set to false
            )
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['show_filter'] = $options['show_filter'];
        $view->vars['unit']['type'] = $this->measureManager->getUnitSymbolsForFamily($options['field_options']['family']);
        $view->vars['value']['type'] = 'number';
        $view->vars['operator']['type'] = $this->getOperatorChoices();

        $view->vars['formatter_options'] = $this->getFormatterOptions($options);
//         $view->vars['unit']['type']  = 'choice';
    }

    protected function getFormatterOptions(array $options)
    {
        $dataType = self::DATA_INTEGER;
        if (isset($options['data_type'])) {
            $dataType = $options['data_type'];
        }

        $formatterOptions = array();

        switch ($dataType) {
            case self::DATA_DECIMAL:
                $formatterOptions['decimals'] = 2;
                $formatterOptions['grouping'] = true;
                break;
            case self::DATA_INTEGER:
            default:
                $formatterOptions['decimals'] = 0;
                $formatterOptions['grouping'] = false;
        }

        $formatter = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::DECIMAL);

        $formatterOptions['orderSeparator'] = $formatterOptions['grouping']
            ? $formatter->getSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL)
            : '';

        $formatterOptions['decimalSeparator'] = $formatter->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);

        return $formatterOptions;
    }
}
