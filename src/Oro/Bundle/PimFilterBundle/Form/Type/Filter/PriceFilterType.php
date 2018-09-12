<?php

namespace Oro\Bundle\PimFilterBundle\Form\Type\Filter;

use Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Price filter type for products
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceFilterType extends AbstractType
{
    /** @staticvar string */
    const NAME = 'pim_type_price_filter';

    /**
     * @var CurrencyRepositoryInterface
     */
    protected $currencyRepository;

    /**
     * @param CurrencyRepositoryInterface $currencyRepository
     */
    public function __construct(CurrencyRepositoryInterface $currencyRepository)
    {
        $this->currencyRepository = $currencyRepository;
    }

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
        return NumberFilterType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('currency', ChoiceType::class, $this->createCurrencyOptions($options));
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
        $result = ['required' => false];
        if ($options['currency_choices']) {
            $result['choices'] = $options['currency_choices'];
        }
        $result = array_merge($result, $options['currency_options']);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $currencyChoices = $this->currencyRepository->getActivatedCurrencyCodes();

        $resolver->setDefaults(
            [
                'data_type'        => NumberFilterType::DATA_DECIMAL,
                'currency_choices' => array_combine($currencyChoices, $currencyChoices),
                'currency_options' => []
            ]
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
