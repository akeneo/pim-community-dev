<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\EnrichBundle\Form\Subscriber\BindGroupProductsSubscriber;
use Pim\Bundle\EnrichBundle\Form\Subscriber\DisableFieldSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Type for group form
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupType extends AbstractType
{
    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var string */
    protected $attributeClass;

    /** @var EventSubscriberInterface[] */
    protected $subscribers = [];

    /** @var string */
    protected $dataClass;

    /**
     * Constructor
     *
     * @param ProductRepositoryInterface $productRepository
     * @param string                     $attributeClass
     * @param string                     $dataClass
     */
    public function __construct(ProductRepositoryInterface $productRepository, $attributeClass, $dataClass)
    {
        $this->productRepository = $productRepository;
        $this->attributeClass    = $attributeClass;
        $this->dataClass         = $dataClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code')
            ->addEventSubscriber(new DisableFieldSubscriber('code'));

        $this->addTypeField($builder);

        $this->addLabelField($builder);

        $this->addAttributesField($builder);

        $this->addProductsField($builder);

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
                'data_class' => $this->dataClass,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_enrich_group';
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

    /**
     * Add type field
     *
     * @param FormBuilderInterface $builder
     */
    protected function addTypeField(FormBuilderInterface $builder)
    {
        $builder
            ->add(
                'type',
                'entity',
                [
                    'class'         => 'PimCatalogBundle:GroupType',
                    'query_builder' => function (EntityRepository $repository) {
                        return $repository->getAllGroupsExceptVariantQB();
                    },
                    'multiple' => false,
                    'expanded' => false,
                    'select2'  => true
                ]
            )
            ->addEventSubscriber(new DisableFieldSubscriber('type', 'getType'));
    }

    /**
     * Add label field
     *
     * @param FormBuilderInterface $builder
     */
    protected function addLabelField(FormBuilderInterface $builder)
    {
        $builder->add(
            'label',
            'pim_translatable_field',
            [
                'field'             => 'label',
                'translation_class' => 'Pim\\Bundle\\CatalogBundle\\Entity\\GroupTranslation',
                'entity_class'      => 'Pim\\Bundle\\CatalogBundle\\Entity\\Group',
                'property_path'     => 'translations'
            ]
        );
    }

    /**
     * Add attributes field
     *
     * @param FormBuilderInterface $builder
     */
    protected function addAttributesField(FormBuilderInterface $builder)
    {
        $builder
            ->add(
                'attributes',
                'entity',
                [
                    'label'         => 'Axis',
                    'required'      => true,
                    'multiple'      => true,
                    'class'         => $this->attributeClass,
                    'query_builder' => function (AttributeRepositoryInterface $repository) {
                        return $repository->findAllAxisQB();
                    },
                    'help'     => 'pim_enrich.group.axis.help',
                    'select2'  => true
                ]
            )
            ->addEventSubscriber(new DisableFieldSubscriber('attributes'));
    }

    /**
     * Add products field with append/remove hidden fields
     *
     * @param FormBuilderInterface $builder
     */
    protected function addProductsField(FormBuilderInterface $builder)
    {
        $builder
            ->add(
                'appendProducts',
                'pim_object_identifier',
                [
                    'repository' => $this->productRepository,
                    'required'   => false,
                    'mapped'     => false,
                    'multiple'   => true
                ]
            )
            ->add(
                'removeProducts',
                'pim_object_identifier',
                [
                    'repository' => $this->productRepository,
                    'required'   => false,
                    'mapped'     => false,
                    'multiple'   => true
                ]
            )
            ->addEventSubscriber(new BindGroupProductsSubscriber($this->productRepository));
    }
}
