<?php

namespace Pim\Bundle\CatalogBundle\Form\Type;

use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\CurrencyRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\LocaleRepository;
use Pim\Bundle\CatalogBundle\Helper\LocaleHelper;
use Pim\Bundle\CatalogBundle\Helper\SortHelper;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pim\Bundle\CatalogBundle\Form\Subscriber\ChannelSubscriber;

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
     * @var \Pim\Bundle\CatalogBundle\Manager\LocaleManager
     */
    protected $localeManager;

    /**
     * @var \Pim\Bundle\CatalogBundle\Helper\LocaleHelper
     */
    protected $localeHelper;

    /**
     * Inject locale manager in the constructor
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
        parent::buildForm($builder, $options);

        $builder->add('id', 'hidden');

        $builder->add('code');

        $builder->add('name', 'text', array('label' => 'Default label'));

        $builder->addEventSubscriber(new ChannelSubscriber());

        $this->addCurrencyField($builder);

        $this->addLocaleField($builder);

        $this->addCategoryField($builder);
    }

    /**
     * Create a currency field and add it to the form builder
     *
     * @param FormBuilderInterface $builder
     *
     * @return null
     */
    protected function addCurrencyField(FormBuilderInterface $builder)
    {
        $builder->add(
            'currencies',
            'entity',
            array(
                'required'      => true,
                'multiple'      => true,
                'class'         => 'Pim\Bundle\CatalogBundle\Entity\Currency',
                'query_builder' => function (CurrencyRepository $repository) {
                    return $repository->getActivatedCurrenciesQB();
                }
            )
        );
    }

    /**
     * Create a locale field and add it to the form builder
     *
     * @param FormBuilderInterface $builder
     *
     * @return null
     */
    protected function addLocaleField(FormBuilderInterface $builder)
    {
        $builder->add(
            'locales',
            'entity',
            array(
                'by_reference'  => false,
                'required'      => true,
                'multiple'      => true,
                'class'         => 'Pim\Bundle\CatalogBundle\Entity\Locale',
                'query_builder' => function (LocaleRepository $repository) {
                    return $repository->getLocalesQB();
                },
                'preferred_choices' => $this->localeManager->getActiveLocales()
            )
        );
    }

    /**
     * Create a category field and add it to the form builder
     * This field only display trees (channel is linked to tree)
     *
     * @param FormBuilderInterface $builder
     *
     * @return null
     */
    protected function addCategoryField(FormBuilderInterface $builder)
    {
        $builder->add(
            'category',
            'entity',
            array(
                'label'         => 'Category tree',
                'required'      => true,
                'class'         => 'Pim\Bundle\CatalogBundle\Entity\Category',
                'query_builder' => function (CategoryRepository $repository) {
                    return $repository->getTreesQB();
                }
            )
        );
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
            $locale->label = $this->localeHelper->getLocalizedLabel($locale->label);
        }
        foreach ($locales->vars['preferred_choices'] as $locale) {
            $locale->label = $this->localeHelper->getLocalizedLabel($locale->label);
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
                'data_class' => 'Pim\Bundle\CatalogBundle\Entity\Channel'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product_channel';
    }
}
