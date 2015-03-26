<?php

namespace Pim\Bundle\EnrichBundle\Form\Type\MassEditAction;

use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Form type of the Classify operation
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ClassifyType extends AbstractType
{
    /** @var string $categoryClass */
    protected $categoryClass;

    /** @var CategoryManager $categoryManager */
    protected $categoryManager;

    /** @var string $dataClass */
    protected $dataClass;

    /** @var CategoryInterface[] */
    protected $trees;

    /**
     * Constructor.
     *
     * @param string          $categoryClass
     * @param CategoryManager $categoryManager
     * @param string          $dataClass
     */
    public function __construct($categoryClass, $categoryManager, $dataClass)
    {
        $this->categoryClass   = $categoryClass;
        $this->categoryManager = $categoryManager;
        $this->dataClass       = $dataClass;
        $this->trees           = $categoryManager->getEntityRepository()->findBy(['parent' => null]);
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
                    'class'    => $this->categoryClass,
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
                    'class'    => $this->categoryClass,
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
    public function setDefaultOptions(OptionsResolverInterface $resolver)
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
     * @return CategoryInterface[]
     */
    public function getTrees()
    {
        return $this->trees;
    }
}
