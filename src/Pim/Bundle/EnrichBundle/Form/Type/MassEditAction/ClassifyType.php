<?php

namespace Pim\Bundle\EnrichBundle\Form\Type\MassEditAction;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
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
    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var string */
    protected $dataClass;

    /** @var CategoryInterface[] */
    protected $trees;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param string                      $dataClass
     */
    public function __construct(CategoryRepositoryInterface $categoryRepository, $dataClass)
    {
        $this->categoryRepository = $categoryRepository;
        $this->dataClass          = $dataClass;
        $this->trees              = $categoryRepository->getTrees();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'trees',
                'oro_entity_identifier',
                [
                    'class'    => $this->categoryRepository->getClassName(),
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                ]
            );

        $builder
            ->add(
                'categories',
                'oro_entity_identifier',
                [
                    'class'    => $this->categoryRepository->getClassName(),
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
        $view->vars['trees'] = $this->getTrees();
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => $this->dataClass
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_enrich_mass_classify';
    }

    /**
     * @deprecated Will be removed in 1.6, this method should not be called as we expose the trees in the form view
     * @return CategoryInterface[]
     */
    public function getTrees()
    {
        return $this->trees;
    }
}
