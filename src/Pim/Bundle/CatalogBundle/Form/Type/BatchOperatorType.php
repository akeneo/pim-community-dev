<?php

namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Pim\Bundle\CatalogBundle\Form\Subscriber\BatchProduct\AddSelectedOperationSubscriber;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BatchOperatorType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('productIds', 'hidden', array(
                'multiple' => true,
            ))
            ->add('operationAlias', 'choice', array(
                'choices'  => $options['operations'],
                'expanded' => true,
                'multiple' => false,
            ))
            ->addEventSubscriber(new AddSelectedOperationSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'operations' => array(),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_catalog_batch_operation';
    }
}
