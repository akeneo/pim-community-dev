<?php

namespace Pim\Bundle\EnrichBundle\Form\Type\MassEditAction;

use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type of the ChangeFamily operation
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChangeFamilyType extends AbstractType
{
    /** @var string */
    protected $dataClass;

    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    /**
     * @param string                    $dataClass
     * @param FamilyRepositoryInterface $familyRepository
     */
    public function __construct($dataClass, FamilyRepositoryInterface $familyRepository)
    {
        $this->dataClass = $dataClass;
        $this->familyRepository = $familyRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'family',
            'pim_async_select',
            [
                'repository' => $this->familyRepository,
                'route'      => 'pim_enrich_family_rest_index',
                'required'   => false,
                'attr'       => [
                    'data-placeholder' => 'pim_enrich.mass_edit_action.change-family.no_family'
                ],
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
    public function getBlockPrefix()
    {
        return 'pim_enrich_mass_change_family';
    }
}
