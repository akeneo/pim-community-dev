<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Type for Association
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationType extends AbstractType
{
    /** @var string */
    protected $productClass;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var EntityRepository */
    protected $groupRepository;

    /** @var EntityRepository */
    protected $assocTypeRepository;

    /** @var string */
    protected $dataClass;

    /**
     * Constructor
     *
     * @param ProductRepositoryInterface $productRepository
     * @param EntityManager              $entityManager
     * @param string                     $productClass
     * @param string                     $assocTypeClass
     * @param string                     $groupClass
     * @param string                     $dataClass
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        EntityManager $entityManager,
        $productClass,
        $assocTypeClass,
        $groupClass,
        $dataClass
    ) {
        $this->productClass = $productClass;
        $this->productRepository = $productRepository;

        $this->groupRepository = $entityManager->getRepository($groupClass);
        $this->assocTypeRepository = $entityManager->getRepository($assocTypeClass);
        $this->dataClass = $dataClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'associationType',
                'pim_object_identifier',
                [
                    'repository' => $this->assocTypeRepository,
                    'multiple'   => false
                ]
            )
            ->add(
                'appendProducts',
                'pim_object_identifier',
                [
                    'repository' => $this->productRepository,
                    'mapped'     => false,
                    'required'   => false,
                    'multiple'   => true
                ]
            )
            ->add(
                'removeProducts',
                'pim_object_identifier',
                [
                    'repository' => $this->productRepository,
                    'mapped'     => false,
                    'required'   => false,
                    'multiple'   => true
                ]
            )
            ->add(
                'appendGroups',
                'pim_object_identifier',
                [
                    'repository' => $this->groupRepository,
                    'mapped'     => false,
                    'required'   => false,
                    'multiple'   => true
                ]
            )
            ->add(
                'removeGroups',
                'pim_object_identifier',
                [
                    'repository' => $this->groupRepository,
                    'mapped'     => false,
                    'required'   => false,
                    'multiple'   => true
                ]
            );
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
        return 'pim_enrich_association';
    }
}
