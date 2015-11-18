<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use Pim\Bundle\EnrichBundle\Form\DataTransformer\StringToBooleanTransformer;
use Pim\Bundle\EnrichBundle\Form\Subscriber\BindAssociationTargetsSubscriber;
use Pim\Bundle\EnrichBundle\Form\View\ProductFormViewInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Product edit form type
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductEditType extends AbstractType
{
    /** @var ProductFormViewInterface */
    protected $productFormView;

    /** @var FamilyRepositoryInterface */
    protected $repository;

    /** @var string */
    protected $categoryClass;

    /** @var EventSubscriberInterface[] */
    protected $subscribers = [];

    /**
     * Constructor
     *
     * @param ProductFormViewInterface  $productFormView
     * @param FamilyRepositoryInterface $repository
     * @param string                    $categoryClass
     */
    public function __construct(
        ProductFormViewInterface $productFormView,
        FamilyRepositoryInterface $repository,
        $categoryClass
    ) {
        $this->productFormView = $productFormView;
        $this->repository      = $repository;
        $this->categoryClass   = $categoryClass;
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
        $transformer = new StringToBooleanTransformer();

        if ($options['enable_state']) {
            $builder->add(
                $builder->create('enabled', 'hidden')->addModelTransformer($transformer)
            );
        }
        $builder
            ->add(
                'associations',
                'collection',
                [
                    'type' => 'pim_enrich_association'
                ]
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
                    'required'           => false,
                ]
            );
        }

        $builder
            ->add(
                'categories',
                'oro_entity_identifier',
                [
                    'class'    => $this->categoryClass,
                    'required' => true,
                    'mapped'   => true,
                    'multiple' => true,
                ]
            );

        foreach ($this->subscribers as $subscriber) {
            $builder->addEventSubscriber($subscriber);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'enable_family' => true,
                'enable_state'  => true
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['groups'] = $this->productFormView->getView();
    }

    /**
     * Add an event subscriber
     *
     * @param EventSubscriberInterface $subscriber
     */
    public function addEventSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->subscribers[] = $subscriber;
    }
}
