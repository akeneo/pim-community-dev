<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\FamilySearchableRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Product creation form type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCreateType extends AbstractType
{
    protected $familyRepository;

    public function __construct(IdentifiableObjectRepositoryInterface $familyRepository)
    {
        $this->familyRepository = $familyRepository;
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
                [
                    'type'               => 'pim_product_value',
                    'allow_add'          => true,
                    'allow_delete'       => true,
                    'by_reference'       => false,
                    'cascade_validation' => true,
                ]
            )
            ->add(
                'family',
                'pim_ajax_select',
                [
                    'repository' => $this->familyRepository,
                    'route'      => 'pim_enrich_family_rest_index',
                    'required'   => false,
                    'select2'    => true,
                    'attr'       => [
                        'data-placeholder' => 'Choose a family'
                    ],
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product_create';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'pim_product';
    }
}
