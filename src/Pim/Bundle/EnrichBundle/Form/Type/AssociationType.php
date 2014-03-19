<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Pim\Bundle\CatalogBundle\Model\ProductRepositoryInterface;
use Doctrine\ORM\EntityManager;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Type for Association
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationType extends AbstractType
{
    /**
     * @var string
     */
    protected $productClass;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var EntityRepository
     */
    protected $groupRepository;

    /**
     * @var EntityRepository
     */
    protected $assocTypeRepository;

    /**
     * Costructor
     *
     * @param string                     $productClass
     * @param ProductRepositoryInterface $productRepository
     * @param EntityManager              $entityManager
     * @param string                     $assocTypeClass
     * @param string                     $groupClass
     */
    public function __construct(
        $productClass,
        ProductRepositoryInterface $productRepository,
        EntityManager $entityManager,
        $assocTypeClass,
        $groupClass
    ) {
        $this->productClass = $productClass;
        $this->productRepository = $productRepository;

        $this->groupRepository = $entityManager->getRepository($groupClass);
        $this->assocTypeRepository = $entityManager->getRepository($assocTypeClass);
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
                array(
                    'repository' => $this->assocTypeRepository,
                    'multiple' => false
                )
            )
            ->add(
                'appendProducts',
                'pim_object_identifier',
                array(
                    'repository' => $this->productRepository,
                    'mapped'   => false,
                    'required' => false,
                    'multiple' => true
                )
            )
            ->add(
                'removeProducts',
                'pim_object_identifier',
                array(
                    'repository' => $this->productRepository,
                    'mapped'   => false,
                    'required' => false,
                    'multiple' => true
                )
            )
            ->add(
                'appendGroups',
                'pim_object_identifier',
                array(
                    'repository' => $this->groupRepository,
                    'mapped'   => false,
                    'required' => false,
                    'multiple' => true
                )
            )
            ->add(
                'removeGroups',
                'pim_object_identifier',
                array(
                    'repository' => $this->groupRepository,
                    'mapped'   => false,
                    'required' => false,
                    'multiple' => true
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
                'data_class' => 'Pim\Bundle\CatalogBundle\Model\Association'
            )
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
