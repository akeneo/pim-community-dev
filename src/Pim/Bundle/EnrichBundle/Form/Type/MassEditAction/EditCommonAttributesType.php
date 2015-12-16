<?php

namespace Pim\Bundle\EnrichBundle\Form\Type\MassEditAction;

use Pim\Bundle\CatalogBundle\Helper\LocaleHelper;
use Pim\Bundle\EnrichBundle\Form\View\ProductFormViewInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type of the EditCommonAttributes operation
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditCommonAttributesType extends AbstractType
{
    /** @var ProductFormViewInterface $productFormView */
    protected $productFormView;

    /** @var LocaleHelper $localeHelper */
    protected $localeHelper;

    /** @var string */
    protected $attributeClass;

    /** @var string */
    protected $localeClassName;

    /** @var string */
    protected $dataClass;

    /**
     * @param ProductFormViewInterface $productFormView
     * @param LocaleHelper             $localeHelper
     * @param string                   $attributeClass
     * @param string                   $localeClassName
     * @param string                   $dataClass
     */
    public function __construct(
        ProductFormViewInterface $productFormView,
        LocaleHelper $localeHelper,
        $attributeClass,
        $localeClassName,
        $dataClass
    ) {
        $this->productFormView = $productFormView;
        $this->localeHelper    = $localeHelper;
        $this->attributeClass  = $attributeClass;
        $this->localeClassName = $localeClassName;
        $this->dataClass       = $dataClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('values', 'hidden');
        $builder->add('current_locale', 'hidden');
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => $this->dataClass]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_enrich_mass_edit_common_attributes';
    }
}
