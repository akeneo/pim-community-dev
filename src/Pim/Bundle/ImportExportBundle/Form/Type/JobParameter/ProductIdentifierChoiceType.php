<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type\JobParameter;

use Pim\Component\Catalog\Validator\Constraints\ValidIdentifier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

/**
 * Product identifier choice type - build a list of product identifiers with a select2
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductIdentifierChoiceType extends AbstractType
{
    /** @var RouterInterface $router */
    protected $router;
    
    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       $builder->add('product_identifier', 'hidden', [
           'constraints' => new ValidIdentifier()
       ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $products = array_map(
            function ($identifier) {
                $identifier = trim($identifier);

                return ['id' => $identifier, 'text' => $identifier];
            },
            explode(',', $form->get('product_identifier')->getData())
        );

        $view->vars['choices'] = json_encode($products);
        $view->vars['url'] = $this->router->generate($options['route']);
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
            'inherit_data'      => true,
            'route_parameters'  => [],
            'multiple'          => false,
            'placeholder'       => null,
        ]);

        $resolver->setDefined([
            'route_parameters',
            'placeholder',
            'multiple',
        ]);

        $resolver->setRequired(['route']);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product_identifier_choice';
    }
}
