<?php

namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\BatchProduct;
use Pim\Bundle\CatalogBundle\Form\Subscriber\ScopeBatchProductQueryStringSubscriber;

/**
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BatchProductType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('products', 'entity', array(
                'class'     => 'Pim\\Bundle\\CatalogBundle\\Entity\\Product',
                'multiple'  => true,
                'read_only' => true,
            ))
            ->add('operation', 'choice', array(
                'choices' => BatchProduct::getOperationChoices(),
                'expanded' => true,
                'multiple' => false,
            ))
            ->addEventSubscriber(new ScopeBatchProductQueryStringSubscriber());
    }

    public function getName()
    {
        return 'batch_products';
    }
}
