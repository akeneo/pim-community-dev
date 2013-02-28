<?php
namespace Pim\Bundle\ConfigBundle\Form\Type;

use Symfony\Component\Locale\Locale;

use Symfony\Component\Locale\Stub\StubLocale;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\AbstractType;

/**
 * Type for currency form
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CurrencyType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('id', 'hidden');

        $currencies = StubLocale::getDisplayCurrencies(\Locale::getDefault());
        asort($currencies);

        $builder->add(
            'code',
            'choice',
            array(
                'choices' => $currencies,
                'required' => true,
                'preferred_choices' => array('USD', 'EUR')
            )
        );

        $builder->add('activated', 'checkbox', array('required' => false));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Pim\Bundle\ConfigBundle\Entity\Currency'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_config_currency';
    }
}
