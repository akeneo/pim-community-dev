<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
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
                $options['family_column'],
                'pim_import_entity',
                array(
                    'property_path' => 'family',
                    'class'         => 'PimCatalogBundle:Family',
                )
            )
            ->add(
                $options['categories_column'],
                'pim_import_entity',
                array(
                    'property_path' => 'categories',
                    'class'         => 'PimCatalogBundle:Category',
                    'multiple'      => true,
                )
            )
            ->add(
                $options['groups_column'],
                'pim_import_entity',
                array(
                    'property_path' => 'groups',
                    'class'         => 'PimCatalogBundle:Group',
                    'multiple'      => true,
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

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection'   => false,
                'family_column'     => 'family',
                'categories_column' => 'category',
                'groups_column'     => 'groups'
            )
        );
    }
}
