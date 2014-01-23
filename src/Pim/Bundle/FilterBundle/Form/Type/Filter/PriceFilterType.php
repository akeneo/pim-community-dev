<?php

namespace Pim\Bundle\FilterBundle\Form\Type\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;

/**
 * Price filter type for products
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceFilterType extends AbstractType
{
    /**
     * @staticvar string
     */
    const NAME = 'pim_type_price_filter';

    /**
     * @var CurrencyManager
     */
    protected $currencyManager;

    /**
     * @param CurrencyManager $currencyManager
     */
    public function __construct(CurrencyManager $currencyManager)
    {
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
     * {@inheritdoc}
     */
    public function getParent()
    {
        return NumberFilterType::NAME;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $currencyChoices = $this->currencyManager->getActiveCodeChoices();

        $resolver->replaceDefaults(array('data_type' => NumberFilterType::DATA_DECIMAL));
        $resolver->setDefaults(
            array(
                'currency_choices' => $currencyChoices,
                'currency_options' => array()
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars['currency_choices'] = $options['currency_choices'];
    }
}
