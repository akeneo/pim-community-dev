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
     * List of existing currencies
     * @var array
     */
    protected $currencies;

    /**
     * Constructor
     * @param array $config
     */
    public function __construct($config = array())
    {
        $this->currencies = $config['currencies'];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('id', 'hidden');

        $builder->add(
            'code',
            'choice',
            array(
                'choices' => static::prepareCurrencyList($this->currencies),
                'required' => true,
                'preferred_choices' => array('USD', 'EUR')
            )
        );

        $builder->add('activated', 'hidden');
    }

    /**
     * Prepare currency list
     * @param array $currencies
     *
     * @return multitype:string
     *
     * @static
     */
    protected static function prepareCurrencyList($currencies = array())
    {
        $choices = array();

        foreach ($currencies as $code => $currency) {
            $choices[$code] = $currency['label'];
        }

        // Sort choices by alpĥabetical
        asort($choices);

        return $choices;
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
