<?php

namespace Pim\Bundle\FilterBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

/**
 * Form type for category filter type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryType extends AbstractType
{
    const NAME = 'pim_type_category';

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'categoryId',
                'integer',
                array(
                    'required' => false,
                )
            )
            ->add(
                'treeId',
                'integer',
                array(
                    'required' => false,
                )
            );
    }

    /**
     * {@inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['value']['treeId']     = $form->get('treeId')->getViewData();
        $view->vars['value']['categoryId'] = $form->get('categoryId')->getViewData();
    }
}
