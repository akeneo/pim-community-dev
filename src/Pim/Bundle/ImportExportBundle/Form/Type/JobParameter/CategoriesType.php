<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type\JobParameter;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Oro\Bundle\FormBundle\Form\DataTransformer\ArrayToStringTransformer;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Categories selector for the product export builder
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoriesType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('categories_included', 'hidden');
        $builder->add('categories_excluded', 'hidden');

        $builder->get('categories_included')->addViewTransformer(new ArrayToStringTransformer(',', false));
        $builder->get('categories_excluded')->addViewTransformer(new ArrayToStringTransformer(',', false));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'inherit_data' => true,
            'label'        => 'pim_connector.export.categories.label',
            'help'         => 'pim_connector.export.categories.help',
            'attr'         => ['data-tab' => 'content']
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_import_export_product_export_categories';
    }
}
