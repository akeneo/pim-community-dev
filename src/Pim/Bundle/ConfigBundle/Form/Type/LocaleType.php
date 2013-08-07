<?php
namespace Pim\Bundle\ConfigBundle\Form\Type;

use Pim\Bundle\ConfigBundle\Entity\Repository\CurrencyRepository;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\ConfigBundle\Form\Subscriber\LocaleSubscriber;

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
     * @var EntityManager
     */
    protected $em;

    /**
     * List of existing locales
     * @var array
     */
    protected $locales;

    /**
     * Constructor
     * @param EntityManager $em     Entity manager
     * @param array         $config Locales config
     */
    public function __construct(EntityManager $em, $config = array())
    {
        $this->em = $em;
        $this->locales = $config['locales'];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('id', 'hidden');

        $this->addCurrencyField($builder);

        $this->addSubscriber($builder);
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
     * Find all locales that have fallback defined
     *
     * @return array:string
     */
    protected function getLocalesWithFallback()
    {
        return $this->em->getRepository('PimConfigBundle:Locale')->findWithFallback();
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
                'query_builder' => function (CurrencyRepository $repository) {
                    return $repository->getActivatedCurrenciesQB();
                },
                'required'      => true,
                'label'         => 'Default currency (to display)'
            )
        );
    }

    /**
     * Add event subscriber
     * @param FormBuilderInterface $builder
     *
     * @TODO : Explain what is the objective of this method
     */
    protected function addSubscriber(FormBuilderInterface $builder)
    {
        $factory = $builder->getFormFactory();

        $locales = $this->prepareLocaleList($this->locales);

        $existingLocales = array_map(
            function ($locale) {
                return $locale->getCode();
            },
            $this->em->getRepository('PimConfigBundle:Locale')->findAll()
        );

        $localesWithFallback = $this->getLocalesWithFallback();

        $subscriber = new LocaleSubscriber($factory, $locales, $existingLocales, $localesWithFallback);
        $builder->addEventSubscriber($subscriber);
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
