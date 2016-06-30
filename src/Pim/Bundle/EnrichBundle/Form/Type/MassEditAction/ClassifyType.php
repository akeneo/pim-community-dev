<?php

namespace Pim\Bundle\EnrichBundle\Form\Type\MassEditAction;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type of the Classify operation
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ClassifyType extends AbstractType
{
    /** @var string */
    protected $dataClass;

    /** @var string */
    protected $formName;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param string                      $dataClass
     * @param string                      $formName
     */
    public function __construct(CategoryRepositoryInterface $categoryRepository, $dataClass, $formName)
    {
        $this->categoryRepository = $categoryRepository;
        $this->dataClass          = $dataClass;
        $this->formName           = $formName;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $categoryClassName = $this->categoryRepository->getClassName();

        $builder->add(
            'trees',
            'oro_entity_identifier',
            [
                'class'    => $categoryClassName,
                'required' => false,
                'mapped'   => false,
                'multiple' => true,
            ]
        );

        $builder->add(
            'categories',
            'oro_entity_identifier',
            [
                'class'    => $categoryClassName,
                'required' => true,
                'mapped'   => true,
                'multiple' => true,
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['trees'] = $this->categoryRepository->findBy(['parent' => null]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->dataClass
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->formName;
    }
}
