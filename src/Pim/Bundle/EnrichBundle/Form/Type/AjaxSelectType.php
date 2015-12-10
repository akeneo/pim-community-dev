<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\EnrichBundle\Form\DataTransformer\IdentifiableModelTransformer;
use Pim\Bundle\EnrichBundle\Form\Factory\IdentifiableModelTransformerFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

/**
 * Select form type
 * A form type to display a select2 dropdown with AJAX autocompletion
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AjaxSelectType extends AbstractType
{
    /** @var RouterInterface */
    protected $router;

    /** @var mixed */
    protected $transformerFactory;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router, $transformerFactory)
    {
        $this->router             = $router;
        $this->transformerFactory = $transformerFactory;
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
                    'repository_options' => [],
                    'route_parameters'   => [],
                    'required'           => false,
                    'multiple'           => false,
                    'min-input-length'   => 0,
                ]
            )
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
                '\Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface'
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
        $view->vars['attr']['data-url']              = $this->getUrl($options);
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
        return $this->transformerFactory->create($repository, $options);
    }
}
