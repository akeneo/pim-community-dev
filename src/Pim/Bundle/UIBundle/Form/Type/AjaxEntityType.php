<?php

namespace Pim\Bundle\UIBundle\Form\Type;

use Pim\Bundle\UIBundle\Form\Transformer\AjaxEntityTransformerFactory;
use Pim\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Ajax entity type
 *
 * An entity type with asynchronously loaded options. The repository entity's doctrine repository
 * class must implement OptionRepositoryInterface
 *
 * Defined options are :
 *   - class:                   The class of the entity
 *   - multiple:                True for multiple fields
 *   - transformer_options:     Extra options which should be passed to the transformer
 *   - collection_id:           The collection id which should be passed to the AjaxOptionController
 *   - route:                   The route for the AjaxOptionController list action
 *   - route_parameters:        Extra parameters for this route
 *   - minimum_input_length:    The minimum query length before a search is run
 *   - url:                     URL for the list action (optional, resolved from route and route_parameters)
 *   - locale:                  The locale of the results (optional, queried through the UserContext)
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AjaxEntityType extends AbstractType
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var AjaxEntityTransformerFactory
     */
    protected $transformerFactory;

    /**
     * @var UserContext
     */
    protected $userContext;

    /**
     * Constructor
     *
     * @param RouterInterface              $router
     * @param AjaxEntityTransformerFactory $transformerFactory
     * @param UserContext                  $userContext
     */
    public function __construct(
        RouterInterface $router,
        AjaxEntityTransformerFactory $transformerFactory,
        UserContext $userContext
    ) {
        $this->router             = $router;
        $this->transformerFactory = $transformerFactory;
        $this->userContext        = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_ajax_entity';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'hidden';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer($this->getTransformer($options));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('class'));
        $resolver->setOptional(array('locale', 'url'));
        $resolver->setDefaults(
            array(
                'multiple'              => false,
                'transformer_options'   => array(),
                'collection_id'         => null,
                'route'                 => 'pim_ui_ajaxentity_list',
                'route_parameters'      => array(),
                'data_class'            => null,
                'error_bubbling'        => false,
                'minimum_input_length'  => 0
            )
        );
        $resolver->setNormalizers(
            array(
                'locale' => function (Options $options, $value) {
                    if (!$value) {
                        $value = $this->userContext->getCurrentLocaleCode();
                    }

                    return $value;
                },
                'url' => function (Options $options, $value) {
                    if (!$value) {
                        $parameters = $this->getRouteParameters($options);
                        $value = $this->router->generate($options['route'], $parameters);
                    }

                    return $value;
                }
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ($options['multiple']) {
            $view->vars['attr']['data-multiple'] = 'multiple';
        }
        $view->vars['attr']['class'] =  'pim-ajax-entity' .
            (isset($view->vars['attr']['class']) ? ' ' .  $view->vars['attr']['class'] : '');

        $view->vars['attr']['data-url'] = $options['url'];
        $view->vars['attr']['data-min-input-length'] = $options['minimum_input_length'];
        $view->vars['attr']['data-choices'] = json_encode(
            $this->getTransformer($options)->getOptions($form->getData())
        );
        if ($options['required']) {
            $view->vars['attr']['data-required'] = 'required';
        }
    }

    /**
     * Returns the transformer options
     *
     * @param array $options
     *
     * @return array
     */
    protected function getTransformerOptions(array $options)
    {
        $transformerOptions = $options['transformer_options'];
        $transformerOptions['class'] = $options['class'];
        $transformerOptions['multiple'] = $options['multiple'];
        $transformerOptions['locale'] = $options['locale'];
        $transformerOptions['collection_id'] = $options['collection_id'];

        return $transformerOptions;
    }

    /**
     * Returns the list route parameters
     *
     * @param Options $options
     *
     * @return array
     */
    protected function getRouteParameters(Options $options)
    {
        $parameters = $options['route_parameters'];
        $parameters['class'] = $options['class'];
        $parameters['dataLocale'] = $options['locale'];
        $parameters['collectionId'] = $options['collection_id'];

        return $parameters;
    }

    /**
     * Returns the form type's transformer
     *
     * @param array $options
     *
     * @return \Symfony\Component\Form\DataTransformerInterface
     */
    protected function getTransformer(array $options)
    {
        return $this->transformerFactory->create($this->getTransformerOptions($options));
    }
}
