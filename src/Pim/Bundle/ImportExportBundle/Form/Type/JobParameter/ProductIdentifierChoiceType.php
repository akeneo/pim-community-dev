<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type\JobParameter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Product identifier choice type - build a list of product identifiers with a select2
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductIdentifierChoiceType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('product_identifier', 'hidden');
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $identifiers = $this->normalizeIdentifiers($form->get('product_identifier')->getData());

        $view->vars['choices'] = json_encode($identifiers);
        $view->vars['multiple'] = $options['multiple'];
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->children['product_identifier']->vars['attr']['placeholder'] = $options['placeholder'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'inherit_data' => true,
            'multiple'     => false,
            'placeholder'  => null,
        ]);

        $resolver->setDefined([
            'placeholder',
            'multiple',
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product_identifier_choice';
    }

    /**
     * @param string $data
     *
     * @return array
     */
    protected function normalizeIdentifiers($data)
    {
        return array_map(
            function ($identifier) {
                $identifier = trim($identifier);

                return [$identifier];
            },
            explode(',', $data)
        );
    }
}
