<?php
namespace Pim\Bundle\ConfigBundle\Form\Type;

use Symfony\Component\Locale\Locale;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\AbstractType;

/**
 * Type for locale form
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LocaleType extends AbstractType
{

    /**
     * List of existing locales
     * @var array
     */
    protected $locales;

    /**
     * Constructor
     * @param array $config
     */
    public function __construct($config = array())
    {
        $this->locales = $config['locales'];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('id', 'hidden');

        $this->addLocaleField($builder);

        $this->addFallbackField($builder);

        $this->addCurrencyField($builder);

        $builder->add('activated', 'hidden');
    }

    /**
     * Add locale field
     * @param FormBuilderInterface $builder
     */
    protected function addLocaleField(FormBuilderInterface $builder)
    {
        $builder->add(
            'code',
            'choice',
            array(
                'choices'           => $this->prepareLocaleList($this->locales),
                'required'          => true,
                'preferred_choices' => array('en_EN', 'fr_FR', 'en_US'),
                'label'             => 'Locale'
            )
        );
    }

    /**
     * Add fallback field
     * @param FormBuilderInterface $builder
     */
    protected function addFallbackField(FormBuilderInterface $builder)
    {
        $builder->add(
            'fallback',
            'choice',
            array(
                'choices'           => $this->prepareLocaleList($this->locales),
                'required'          => false,
                'preferred_choices' => array('en_EN', 'fr_FR', 'en_US'),
                'label'             => 'Inherited locale'
            )
        );
    }

    /**
     * Prepare locale list
     * @param array $locales
     *
     * @return multitype:string
     */
    protected function prepareLocaleList($locales = array())
    {
        $choices = array();

        foreach ($locales as $code => $locale) {
            $choices[$code] = $locale['label'];
        }

        asort($choices);

        return $choices;
    }

    /**
     * Add currency field
     * @param FormBuilderInterface $builder
     *
     * @return null
     */
    protected function addCurrencyField(FormBuilderInterface $builder)
    {
        $builder->add(
            'defaultCurrency',
            'entity',
            array(
                'class'         => 'Pim\Bundle\ConfigBundle\Entity\Currency',
                'property'      => 'code',
                'multiple'      => false,
                'query_builder' => function (EntityRepository $repository) {
                    $query = $repository->createQueryBuilder('c');
                    $query->andwhere(
                        $query->expr()->eq('c.activated', true)
                    )
                    ->orderBy('c.code');

                    return $query;
                },
                'required'      => true,
                'label'         => 'Default currency (to display)'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Pim\Bundle\ConfigBundle\Entity\Locale'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_config_locale';
    }
}
