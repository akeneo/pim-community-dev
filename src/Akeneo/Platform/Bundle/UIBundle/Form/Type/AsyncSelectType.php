<?php

namespace Akeneo\Platform\Bundle\UIBundle\Form\Type;

use Akeneo\Platform\Bundle\UIBundle\Form\Factory\IdentifiableModelTransformerFactory;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

/**
 * Select form type
 * A form type to display an asynchronous dropdown
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AsyncSelectType extends AbstractType
{
    /** @var RouterInterface */
    protected $router;

    /** @var IdentifiableModelTransformerFactory */
    protected $transformerFactory;

    /**
     * @param RouterInterface                     $router
     * @param IdentifiableModelTransformerFactory $transformerFactory
     */
    public function __construct(
        RouterInterface $router,
        IdentifiableModelTransformerFactory $transformerFactory
    ) {
        $this->router = $router;
        $this->transformerFactory = $transformerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pim_async_select';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return HiddenType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(
                [
                    'repository_options' => [],
                    'route_parameters'   => [],
                    'required'           => false,
                    'multiple'           => false,
                    'min-input-length'   => 0,
                ]
            )
            ->setAllowedTypes('repository_options', ['array'])
            ->setAllowedTypes('route_parameters', ['array'])
            ->setAllowedTypes('required', ['bool'])
            ->setAllowedTypes('multiple', ['bool'])
            ->setAllowedTypes('min-input-length', ['int'])
            ->setRequired(['route', 'repository']);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $repository = $options['repository'];

        if (!$repository instanceof IdentifiableObjectRepositoryInterface) {
            throw new UnexpectedTypeException(
                $repository,
                '\Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface'
            );
        }

        $builder->addViewTransformer(
            $this->createDataTransformer($repository, $options),
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['attr']['data-url'] = $this->getUrl($options);
        $view->vars['attr']['data-min-input-length'] = $options['min-input-length'];
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

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param array                                 $options
     *
     * @return DataTransformerInterface
     */
    protected function createDataTransformer(IdentifiableObjectRepositoryInterface $repository, $options)
    {
        $transformerOptions = [
            'multiple' => $options['multiple']
        ];
        return $this->transformerFactory->create($repository, $transformerOptions);
    }
}
