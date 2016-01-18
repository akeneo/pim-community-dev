<?php

namespace Pim\Bundle\EnrichBundle\Form\Type\MassEditAction;

use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Add to variant group mass action form type
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddToVariantGroupType extends AbstractType
{
    /** @var string */
    protected $dataClass;

    /** @var string */
    protected $groupClassName;

    /** @var array */
    protected $skippedObjects;

    /** @var GroupRepositoryInterface */
    protected $groupRepository;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var GroupInterface */
    protected $group;

    /** @var ProductMassActionRepositoryInterface */
    protected $prodMassActionRepo;

    /**
     * @param ProductMassActionRepositoryInterface $prodMassActionRepo
     * @param GroupRepositoryInterface             $groupRepository
     * @param string                               $groupClassName
     * @param string                               $dataClass
     */
    public function __construct(
        ProductMassActionRepositoryInterface $prodMassActionRepo,
        GroupRepositoryInterface $groupRepository,
        $groupClassName,
        $dataClass
    ) {
        $this->prodMassActionRepo = $prodMassActionRepo;
        $this->groupClassName     = $groupClassName;
        $this->dataClass          = $dataClass;
        $this->groupRepository    = $groupRepository;

        $this->skippedObjects = [];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'group',
            'entity',
            [
                'class'       => $this->groupClassName,
                'required'    => true,
                'multiple'    => false,
                'expanded'    => false,
                'choices'     => $options['groups'],
                'select2'     => true,
                'empty_value' => '',
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
                'groups'     => $this->getVariantGroups(),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_enrich_mass_add_to_variant_group';
    }

    /**
     * Get valid variant groups to display
     *
     * @return array
     */
    public function getVariantGroups()
    {
        return $this->groupRepository->getAllVariantGroups();
    }
}
