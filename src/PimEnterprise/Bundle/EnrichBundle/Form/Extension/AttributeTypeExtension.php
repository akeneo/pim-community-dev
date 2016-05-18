<?php

namespace PimEnterprise\Bundle\EnrichBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

class AttributeTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options = [])
    {
        $builder->add('isReadOnly', 'switch', [
            'required'      => false,
            'property_path' => 'properties[is_read_only]',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'pim_enrich_attribute';
    }
}
