<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

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
            'pim_translatable_field',
            [
                'field'             => 'label',
                'translation_class' => 'Pim\\Bundle\\CatalogBundle\\Entity\\AssociationTypeTranslation',
                'entity_class'      => 'Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType',
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
    public function getName()
    {
        return 'pim_enrich_associationtype';
    }
}
