<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Form\Extension;

use Pim\Bundle\EnrichBundle\Form\Type\AttributeType;
use Pim\Bundle\UIBundle\Form\Type\SwitchType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class AttributeTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options = [])
    {
        $builder->add('isReadOnly', SwitchType::class, [
            'required'      => false,
            'property_path' => 'properties[is_read_only]',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return AttributeType::class;
    }
}
