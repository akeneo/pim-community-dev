<?php

namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\CurrencyRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\LocaleRepository;
use Pim\Bundle\CatalogBundle\Form\Subscriber\DisableFieldSubscriber;
use Pim\Bundle\CatalogBundle\Helper\LocaleHelper;
use Pim\Bundle\CatalogBundle\Helper\SortHelper;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;

/**
 * Type for channel form
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelType extends AbstractType
{
    /**
     * @var LocaleManager
     */
    protected $localeManager;

    /**
     * @var LocaleHelper
     */
    protected $localeHelper;

    /**
     * Inject locale manager and locale helper in the constructor
     *
     * @param LocaleManager $localeManager
     * @param LocaleHelper  $localeHelper
     */
    public function __construct(LocaleManager $localeManager, LocaleHelper $localeHelper)
    {
        $this->localeManager = $localeManager;
        $this->localeHelper  = $localeHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this
            ->addCodeField($builder)
            ->addLabelField($builder)
            ->addCurrenciesField($builder)
            ->addLocalesField($builder)
            ->addCategoryField($builder)
            ->addConversionUnitFields($builder)
            ->addEventSubscribers($builder);
    }

    /**
     * Create code field
     * @param FormBuilderInterface $builder
     *
     * @return ChannelType
     */
    protected function addCodeField(FormBuilderInterface $builder)
    {
        $builder->add('code');

        return $this;
    }

    /**
     * Create label field
     * @param FormBuilderInterface $builder
     *
     * @return ChannelType
     */
    protected function addLabelField(FormBuilderInterface $builder)
    {
        $builder->add('label', 'text', array('label' => 'Default label'));

        return $this;
    }

    /**
     * Create currencies field
     * @param FormBuilderInterface $builder
     *
     * @return ChannelType
     */
    protected function addCurrenciesField(FormBuilderInterface $builder)
    {
        $builder->add(
            'currencies',
            'entity',
            array(
                'required'      => true,
                'multiple'      => true,
                'select2'       => true,
                'class'         => 'Pim\Bundle\CatalogBundle\Entity\Currency',
                'query_builder' => function (CurrencyRepository $repository) {
                    return $repository->getActivatedCurrenciesQB();
                }
            )
        );

        return $this;
    }

    /**
     * Create conversion units field
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return ChannelType
     */
    protected function addConversionUnitFields(FormBuilderInterface $builder)
    {
        $builder->add('conversionUnits', 'pim_catalog_conversion_units');

        return $this;
    }

    /**
     * Create locales field
     * @param FormBuilderInterface $builder
     *
     * @return ChannelType
     */
    protected function addLocalesField(FormBuilderInterface $builder)
    {
        $builder->add(
            'locales',
            'entity',
            array(
                'required'      => true,
                'multiple'      => true,
                'select2'       => true,
                'by_reference'  => false,
                'class'         => 'Pim\Bundle\CatalogBundle\Entity\Locale',
                'query_builder' => function (LocaleRepository $repository) {
                    return $repository->getLocalesQB();
                },
                'preferred_choices' => $this->localeManager->getActiveLocales()
            )
        );

        return $this;
    }

    /**
     * Create category field
     * @param FormBuilderInterface $builder
     *
     * @return ChannelType
     */
    protected function addCategoryField(FormBuilderInterface $builder)
    {
        $builder->add(
            'category',
            'entity',
            array(
                'label'         => 'Category tree',
                'required'      => true,
                'select2'       => true,
                'class'         => 'Pim\Bundle\CatalogBundle\Entity\Category',
                'query_builder' => function (CategoryRepository $repository) {
                    return $repository->getTreesQB();
                }
            )
        );

        return $this;
    }

    /**
     * Add event subscriber to channel form type
     * @param FormBuilderInterface $builder
     *
     * @return ChannelType
     */
    protected function addEventSubscribers(FormBuilderInterface $builder)
    {
        $builder->addEventSubscriber(new DisableFieldSubscriber('code'));

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * Translate the locale codes to labels in the current user locale
     * and sort them alphabetically
     *
     * This part is done here because of the choices query is executed just before
     * so we can't access to these properties from form events
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if (!isset($view['locales'])) {
            return;
        }

        /** @var array<ChoiceView> $locales */
        $locales = $view['locales'];
        foreach ($locales->vars['choices'] as $locale) {
            $locale->label = $this->localeHelper->getLocaleLabel($locale->label);
        }
        foreach ($locales->vars['preferred_choices'] as $locale) {
            $locale->label = $this->localeHelper->getLocaleLabel($locale->label);
        }

        $locales->vars['choices'] = SortHelper::sortByProperty($locales->vars['choices'], 'label');
        $locales->vars['preferred_choices'] = SortHelper::sortByProperty(
            $locales->vars['preferred_choices'],
            'label'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Pim\Bundle\CatalogBundle\Entity\Channel',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_catalog_channel';
    }
}
