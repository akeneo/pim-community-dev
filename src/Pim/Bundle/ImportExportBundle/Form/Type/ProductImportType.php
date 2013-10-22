<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Pim\Bundle\CatalogBundle\Form\Subscriber\IgnoreMissingFieldDataSubscriber;

/**
 * Description of ProductImportType
 *
 * @author Antoine Guigan <aguigan@qimnet.com>
 */
class ProductImportType extends AbstractType
{
    protected $transformer;
    
    public function __construct(
        EventSubscriberInterface $transformer
    ) {
        $this->transformer     = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'categories',
                'entity',
                array(
                    'class'        => 'PimCatalogBundle:Category',
                    'multiple'     => true,
                    'by_reference' => false
                )
            )
            ->add(
                'groups',
                'entity',
                array(
                    'class'        => 'PimCatalogBundle:Group',
                    'multiple'     => true,
                    'by_reference' => false
                )
            )
            ->addEventSubscriber($this->transformer)
            ->addEventSubscriber(new IgnoreMissingFieldDataSubscriber());
    }
    public function getName()
    {
        return 'pim_product_import';
    }
    public function getParent()
    {
        return 'pim_product';
    }
}
