<?php

namespace PimEnterprise\Bundle\EnrichBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

use Pim\Bundle\EnrichBundle\Form\Type\AvailableAttributesType as PimAvailableAttributesType;

/**
 * Override available attributes type to remove attributes where rights are revoked
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AvailableAttributesType extends PimAvailableAttributesType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'attributes',
            'light_entity',
            [
                'repository' => $this->repository,
                'repository_options' => [
                    'excluded_attribute_ids' => $options['attributes'],
                    'locale_code'            => $this->userContext->getCurrentLocaleCode(),
                    'default_group_label'    => $this->translator->trans(
                        'Other',
                        array(),
                        null,
                        $this->userContext->getCurrentLocaleCode()
                    ),
                ],
                'multiple' => true,
                'expanded' => false,
            ]
        );
    }
}
