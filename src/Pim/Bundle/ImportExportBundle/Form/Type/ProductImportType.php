<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Pim\Bundle\CatalogBundle\Form\Subscriber\IgnoreMissingFieldDataSubscriber;

/**
 * Product edit form type
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductImportType extends AbstractType
{
    /**
     * @var EventSubscriberInterface
     */
    protected $transformer;

    public function __construct(
        EventSubscriberInterface $transformer
    ) {
        $this->transformer     = $transformer;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enabled', 'hidden')
            ->add(
                'family',
                'pim_import_entity',
                array(
                    'class'       => 'PimCatalogBundle:Family',
                )
            )
            ->add(
                'categories',
                'pim_import_entity',
                array(
                    'class'        => 'PimCatalogBundle:Category',
                    'multiple'     => true,
                )
            )
            ->add(
                'groups',
                'pim_import_entity',
                array(
                    'class'        => 'PimCatalogBundle:Group',
                    'multiple'     => true,
                )
            )
            ->addEventSubscriber($this->transformer)
            ->addEventSubscriber(new IgnoreMissingFieldDataSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product_import';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'pim_product';
    }
}
