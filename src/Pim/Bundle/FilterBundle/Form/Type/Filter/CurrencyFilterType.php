<?php

namespace Pim\Bundle\FilterBundle\Form\Type\Filter;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;
use Pim\Bundle\ConfigBundle\Manager\CurrencyManager;

/**
 * Currency filter type for products
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CurrencyFilterType extends NumberFilterType
{
    /**
     * @staticvar string
     */
    const NAME = 'pim_type_currency_filter';

    /**
     * @var CurrencyManager
     */
    protected $currencyManager;

    /**
     * @param TranslatorInterface $translator
     * @param CurrencyManager     $currencyManager
     */
    public function __construct(TranslatorInterface $translator, CurrencyManager $currencyManager)
    {
        parent::__construct($translator);

        $this->currencyManager = $currencyManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return NumberFilterType::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('currency', 'choice', $this->createCurrencyOptions($options));
    }

    /**
     * Create currencies options list
     *
     * @param array $options
     *
     * @return array
     */
    protected function createCurrencyOptions(array $options)
    {
        $result = array('required' => false);
        if ($options['currency_choices']) {
            $result['choices'] = $options['currency_choices'];
        }
        $result = array_merge($result, $options['currency_options']);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $operatorChoices = array(
            self::TYPE_EQUAL => $this->translator->trans('label_type_equal', array(), 'OroFilterBundle'),
            self::TYPE_GREATER_EQUAL =>
                $this->translator->trans('label_type_greater_equal', array(), 'OroFilterBundle'),
            self::TYPE_GREATER_THAN => $this->translator->trans('label_type_greater_than', array(), 'OroFilterBundle'),
            self::TYPE_LESS_EQUAL => $this->translator->trans('label_type_less_equal', array(), 'OroFilterBundle'),
            self::TYPE_LESS_THAN => $this->translator->trans('label_type_less_than', array(), 'OroFilterBundle')
        );

        $codes = $this->currencyManager->getActiveCodes();
        $currencyChoices = array_combine($codes, $codes);

        return $resolver->setDefaults(
            array(
                'field_type' => 'number',
                'operator_choices' => $operatorChoices,
                'operator_type' => 'choice',
                'operator_options' => array(),
                'currency_choices' => $currencyChoices,
                'currency_type' => 'choice',
                'currency_options' => array(),
                'data_type' => self::DATA_DECIMAL,
                'formatter_options' => array()
            )
        );
    }
}
