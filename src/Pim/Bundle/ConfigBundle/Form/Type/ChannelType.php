<?php

namespace Pim\Bundle\ConfigBundle\Form\Type;

use Pim\Bundle\ProductBundle\Entity\Repository\CategoryRepository;
use Pim\Bundle\ConfigBundle\Entity\Repository\CurrencyRepository;
use Pim\Bundle\ConfigBundle\Entity\Repository\LocaleRepository;
use Pim\Bundle\ConfigBundle\Manager\LocaleManager;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

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
     * @var \Pim\Bundle\ConfigBundle\Manager\LocaleManager
     */
    protected $localeManager;

    /**
     * Inject locale manager in the constructor
     *
     * @param \Pim\Bundle\ConfigBundle\Manager\LocaleManager $localeManager
     */
    public function __construct(LocaleManager $localeManager)
    {
        $this->localeManager = $localeManager;
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

        $this->addCurrencyField($builder);

        $this->addLocaleField($builder);

        $this->addCategoryField($builder);
    }

    /**
     * Create a currency field and add it to the form builder
     *
     * @param FormBuilderInterface $builder
     */
    protected function addCurrencyField(FormBuilderInterface $builder)
    {
        $builder->add(
            'currencies',
            'entity',
            array(
                'required'      => true,
                'multiple'      => true,
                'class'         => 'Pim\Bundle\ConfigBundle\Entity\Currency',
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
                'class'         => 'Pim\Bundle\ConfigBundle\Entity\Locale',
                'query_builder' => function (LocaleRepository $repository) {
                    return $repository->getLocalesQB();
                },
                'preferred_choices' => $this->localeManager->getActiveLocales()
            )
        );
    }

    /**
     * Create a category field and add it to the form builder
     * This field only display the tree (channel is linked to tree)
     *
     * @param FormBuilderInterface $builder
     */
    protected function addCategoryField(FormBuilderInterface $builder)
    {
        $builder->add(
            'category',
            'entity',
            array(
                'label'         => 'Category tree',
                'required'      => true,
                'class'         => 'Pim\Bundle\ProductBundle\Entity\Category',
                'query_builder' => function (CategoryRepository $repository) {
                    return $repository->getTreesQB();
                }
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
                'data_class' => 'Pim\Bundle\ConfigBundle\Entity\Channel'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_config_channel';
    }
}
