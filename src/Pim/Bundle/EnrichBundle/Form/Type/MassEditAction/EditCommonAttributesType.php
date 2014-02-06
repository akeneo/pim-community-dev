<?php

namespace Pim\Bundle\EnrichBundle\Form\Type\MassEditAction;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pim\Bundle\EnrichBundle\Form\View\ProductFormView;
use Pim\Bundle\CatalogBundle\Helper\LocaleHelper;

/**
 * Form type of the EditCommonAttributes operation
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditCommonAttributesType extends AbstractType
{
    /**
     * @var ProductFormView $productFormView
     */
    protected $productFormView;

    /**
     * @var LocaleHelper $localeHelper
     */
    protected $localeHelper;

    /**
     * @var string
     */
    protected $attributeClass;

    /**
     * @param ProductFormView $productFormView
     * @param LocaleHelper    $localeHelper
     * @param string          $attributeClass
     */
    public function __construct(
        ProductFormView $productFormView,
        LocaleHelper $localeHelper,
        $attributeClass
    ) {
        $this->productFormView = $productFormView;
        $this->localeHelper    = $localeHelper;
        $this->attributeClass  = $attributeClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'values',
                'collection',
                array(
                    'type' => 'pim_product_value'
                )
            )
            ->add(
                'locale',
                'entity',
                array(
                    'choices' => $options['locales'],
                    'class'   => 'Pim\\Bundle\\CatalogBundle\\Entity\\Locale',
                    'select2' => true,
                    'attr'    => array(
                        'class' => 'operation-param',
                    )
                )
            )
            ->add(
                'displayedAttributes',
                'entity',
                array(
                    'class'    => $this->attributeClass,
                    'choices'  => $options['commonAttributes'],
                    'required' => false,
                    'multiple' => true,
                    'expanded' => false,
                    'group_by' => 'virtualGroup.label',
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view['locale']->vars['choices'] as $choice) {
            $choice->label = $this->localeHelper->getLocaleLabel($choice->label);
        }

        $view->vars['groups'] = $this->productFormView->getView();
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {

        $resolver->setDefaults(
            array(
                'data_class'       => 'Pim\\Bundle\\EnrichBundle\\MassEditAction\\EditCommonAttributes',
                'locales'          => array(),
                'commonAttributes' => array(),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_enrich_mass_edit_common_attributes';
    }
}
