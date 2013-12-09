<?php

namespace Pim\Bundle\UIBundle\Form\Type;

use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\UIBundle\Form\Transformer\AjaxEntityTransformerFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Ajax choice type
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
     * @var LocaleManager
     */
    protected $localeManager;

    /**
     * Constructor
     *
     * @param RouterInterface              $router
     * @param AjaxEntityTransformerFactory $transformerFactory
     * @param LocaleManager                $localeManager
     */
    public function __construct(
        RouterInterface $router,
        AjaxEntityTransformerFactory $transformerFactory,
        LocaleManager $localeManager
    ) {
        $this->router = $router;
        $this->transformerFactory = $transformerFactory;
        $this->localeManager = $localeManager;
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
                'minimum_input_length'  => 0
            )
        );
        $resolver->setNormalizers(
            array(
                'locale' => function (Options $options, $value) {
                    if (!$value) {
                        $value = $this->localeManager->getDataLocale()->getCode();
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
     * @param  array $options
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

    public function getParent()
    {
        return 'hidden';
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

    protected function getTransformer(array $options)
    {
        return $this->transformerFactory->create($this->getTransformerOptions($options));
    }
}
