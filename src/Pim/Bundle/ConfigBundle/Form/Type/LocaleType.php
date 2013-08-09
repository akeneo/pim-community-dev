<?php

namespace Pim\Bundle\ConfigBundle\Form\Type;

use Pim\Bundle\ConfigBundle\Form\Subscriber\LocaleFallbackSubscriber;

use Pim\Bundle\ConfigBundle\Manager\LocaleManager;

use Pim\Bundle\ConfigBundle\Helper\LocaleHelper;

use Symfony\Component\Form\FormInterface;

use Symfony\Component\Form\FormView;

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
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
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
     * @var \Pim\Bundle\ConfigBundle\Helper\LocaleHelper
     */
    protected $localeHelper;

    /**
     * Constructor
     * @param EntityManager $em     Entity manager
     * @param array         $config Locales config
     *
     * TODO
     */
    public function __construct(LocaleManager $localeManager, LocaleHelper $localeHelper)
    {
        $this->localeManager = $localeManager;
        $this->localeHelper = $localeHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('id', 'hidden');

        $builder->add('code', 'text', array('disabled' => true, 'required' => true));

        $this->addFallbackField($builder);

        // TODO : in the subscriber we must remove the current locale

        $this->addCurrencyField($builder);

//         $this->addSubscriber($builder);
    }

    protected function addFallbackField(FormBuilderInterface $builder)
    {
        $formFactory = $builder->getFormFactory();
        $fallbackSubscriber = new LocaleFallbackSubscriber($formFactory, $this->localeManager, $this->localeHelper);
        $builder->addEventSubscriber($fallbackSubscriber);


//         $fallbackCodes = $this->localeManager->getFallbackCodes();
//         $builder->add(
//             'fallback',
//             'choice',
//             array(
//                 'choices' => $fallbackCodes
//             )
//         );
    }

//     public function finishView(FormView $view, FormInterface $form, array $options)
//     {
//         if (!isset($view['code']) && !isset($view['fallback'])) {
//             return;
//         }

//         if (isset($view['code'])) {
//             /** @var FormView $localeCode */
//             $localeCode = $view['code'];
//             $localeCode->vars['value'] = $this->localeHelper->getLocalizedLabel($localeCode->vars['value']);
//         }

//         if (isset($view['fallback'])) {
//             /** @var ChoiceView $localeFallback */
//             $localeFallbacks = $view['fallback'];
//             foreach ($localeFallbacks->vars['choices'] as $locale) {
//                 $locale->label = $this->localeHelper->getLocalizedLabel($locale->label);
//             }
//         }
//     }



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
