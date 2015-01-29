<?php

namespace Pim\Bundle\EnrichBundle\Form\Type\MassEditAction;

use Pim\Bundle\CatalogBundle\Helper\LocaleHelper;
use Pim\Bundle\EnrichBundle\Form\View\ProductFormViewInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
     * @var ProductFormViewInterface $productFormView
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
     * @param ProductFormViewInterface $productFormView
     * @param LocaleHelper             $localeHelper
     * @param string                   $attributeClass
     */
    public function __construct(
        ProductFormViewInterface $productFormView,
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
                'pim_enrich_localized_collection',
                array(
                    'type' => 'pim_product_value',
                    'allow_add'          => false,
                    'allow_delete'       => true,
                    'by_reference'       => false,
                    'cascade_validation' => true,
                    'currentLocale'      => $options['current_locale']
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
                    'choices'  => $options['common_attributes'],
                    'required' => false,
                    'multiple' => true,
                    'expanded' => false,
                    'group_by' => 'group.label',
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
            [
                'data_class' => 'Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes',
                'locales' => [],
                'common_attributes' => [],
                'current_locale'    => null
            ]
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
