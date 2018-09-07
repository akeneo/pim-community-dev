<?php

namespace Oro\Bundle\PimFilterBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Form type for category filter type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryType extends AbstractType
{
    /** @staticvar string */
    const NAME = 'pim_type_category';

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'categoryId',
                IntegerType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'treeId',
                IntegerType::class,
                [
                    'required' => false,
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['value']['treeId'] = $form->get('treeId')->getViewData();
        $view->vars['value']['categoryId'] = $form->get('categoryId')->getViewData();
    }
}
