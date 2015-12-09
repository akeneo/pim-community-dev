<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Bundle\EnrichBundle\Form\DataTransformer\IdentifiableModelTransformer;
use Pim\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class AjaxSelectType extends AbstractType
{
    /** @var RouterInterface */
    protected $router;

    /** @var UserContext */
    protected $userContext;

    /**
     * @param RouterInterface $router
     * @param UserContext     $userContext
     */
    public function __construct(
        RouterInterface $router,
        UserContext $userContext
    ) {
        $this->router      = $router;
        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_ajax_select';
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
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(
                [
                    'repository'         => null,
                    'repository_options' => [],
                    'route'              => 'pim_ui_ajaxentity_list',
                    'route_parameters'   => [],
                    'required'           => false,
                    'multiple'           => false,
                ]
            )
            ->setNormalizer('repository', function (Options $options, $value) {
                if (!$value instanceof IdentifiableObjectRepositoryInterface) {
                    throw new UnexpectedTypeException(
                        'Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface',
                        $value
                    );
                }

                return $value;
            })
            ->setRequired([
                'route',
                'repository',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(
            new IdentifiableModelTransformer($options['repository']),
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['attr']['data-url']              = $this->getUrl($options);
        $view->vars['attr']['data-min-input-length'] = 0;
        if ($options['required']) {
            $view->vars['attr']['data-required'] = 'required';
        }
        $view->vars['attr']['class'] = 'pim-ajax-entity';
    }

    /**
     * @param array $options
     *
     * @return string
     */
    protected function getUrl(array $options)
    {
        return $this->router->generate($options['route']);
    }
}
