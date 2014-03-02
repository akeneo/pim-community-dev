<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pim\Bundle\EnrichBundle\Form\View\ProductFormView;
use Pim\Bundle\EnrichBundle\Form\Subscriber\BindAssociationTargetsSubscriber;
use Pim\Bundle\CatalogBundle\Entity\Repository\FamilyRepository;

/**
 * Product edit form type
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductEditType extends AbstractType
{
    /** @var ProductFormView $productFormView */
    protected $productFormView;

    /** @var FamilyRepository */
    protected $repository;

    /**
     * Constructor
     *
     * @param ProductFormView  $productFormView
     * @param FamilyRepository $repository
     */
    public function __construct(ProductFormView $productFormView, FamilyRepository $repository)
    {
        $this->productFormView = $productFormView;
        $this->repository      = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product_edit';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'pim_product';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['enable_state']) {
            $builder->add('enabled', 'hidden');
        }
        $builder
            ->add(
                'associations',
                'collection',
                array(
                    'type' => 'pim_enrich_association'
                )
            )
            ->get('associations')
            ->addEventSubscriber(new BindAssociationTargetsSubscriber());

        if ($options['enable_family']) {
            $builder->add(
                'family',
                'light_entity',
                [
                    'repository'         => $this->repository,
                    'repository_options' => ['localeCode' => $options['currentLocale']],
                ]
            );
        }

        $builder
            ->add(
                'categories',
                'oro_entity_identifier',
                array(
                    'class'    => 'PimCatalogBundle:Category',
                    'required' => true,
                    'mapped'   => true,
                    'multiple' => true,
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
                'enable_family' => true,
                'enable_state'  => true
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['groups'] = $this->productFormView->getView();
    }
}
