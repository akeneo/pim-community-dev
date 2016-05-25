<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Bundle\EnrichBundle\Form\Factory\IdentifiableModelTransformerFactory;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
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

    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    /**
     * @param RouterInterface           $router
     * @param FamilyRepositoryInterface $familyRepository
     */
    public function __construct(
        RouterInterface $router,
        FamilyRepositoryInterface $familyRepository
    ) {
        $this->router = $router;
        $this->familyRepository = $familyRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_async_select';
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
                    'help'   => 'pute',
                    'label'   => 'pute',
                ]
            )
            ->setAllowedTypes('repository_options', ['array'])
            ->setAllowedTypes('route_parameters', ['array'])
            ->setAllowedTypes('required', ['bool'])
            ->setAllowedTypes('multiple', ['bool'])
            ->setAllowedTypes('min-input-length', ['int'])
            ->setAllowedTypes('label', 'string')
            ->setAllowedTypes('help', 'string')
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
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ($options['multiple']) {
            $view->vars['attr']['data-multiple'] = 'multiple';
        }
        $view->vars['help'] = $options['label'];
        $view->vars['attr']['data-url']              = $this->getUrl($options);
        $view->vars['attr']['data-min-input-length'] = $options['min-input-length'];
        $view->vars['attr']['data-choices'] = json_encode($this->normalizeFamily($form->getData()));
        if ($options['required']) {
            $view->vars['attr']['data-required'] = 'required';
        }
        $view->vars['attr']['class'] = 'pim-ajax-entity';
    }

    protected function normalizeFamily($familyCodes)
    {
        $familyCodes = explode(',', $familyCodes);

        $result = [];
        foreach ($familyCodes as $familyCode) {
            /** @var FamilyInterface $family */
            $family = $this->familyRepository->findOneByIdentifier($familyCode);
            if ($family instanceof FamilyInterface) {
                $familyLabel = $family->getLabel();
                $result[] = ['id' => $familyCode, 'text' => $familyLabel];
            }
        }

        return $result;
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
