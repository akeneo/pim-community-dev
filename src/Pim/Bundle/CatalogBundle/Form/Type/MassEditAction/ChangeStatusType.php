<?php

namespace Pim\Bundle\CatalogBundle\Form\Type\MassEditAction;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Form type of the ChangeStatus operation
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChangeStatusType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('enable', 'checkbox');
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {

        $resolver->setDefaults(
            array(
                'data_class' => 'Pim\\Bundle\\CatalogBundle\\MassEditAction\\ChangeStatus'
            )
        );
    }

    public function getName()
    {
        return 'pim_catalog_operation_change_status';
    }
}
