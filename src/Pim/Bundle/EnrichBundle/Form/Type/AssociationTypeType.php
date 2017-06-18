<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Pim\Bundle\CatalogBundle\Entity\AssociationType as EntityAssociationType;
use Pim\Bundle\CatalogBundle\Entity\AssociationTypeTranslation;
use Pim\Bundle\EnrichBundle\Form\Subscriber\DisableFieldSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Type for association type form
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeType extends AbstractType
{
    /** @var string */
    protected $dataClass;

    /**
     * @param string $dataClass
     */
    public function __construct($dataClass)
    {
        $this->dataClass = $dataClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('code');

        $this->addLabelField($builder);

        $builder->addEventSubscriber(new DisableFieldSubscriber('code'));
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
            TranslatableFieldType::class,
            [
                'field'             => 'label',
                'translation_class' => AssociationTypeTranslation::class,
                'entity_class'      => EntityAssociationType::class,
                'property_path'     => 'translations'
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
                'data_class' => $this->dataClass
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pim_enrich_associationtype';
    }
}
