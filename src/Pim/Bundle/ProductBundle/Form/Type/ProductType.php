<?php
namespace Pim\Bundle\ProductBundle\Form\Type;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleType;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\ProductBundle\Form\View\ProductFormView;

/**
 * Product form type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductType extends FlexibleType
{
    protected $productFormView;

    /**
     * {@inheritdoc}
     */
    public function __construct(FlexibleManager $flexibleManager, $valueFormAlias, ProductFormView $productFormView)
    {
        parent::__construct($flexibleManager, $valueFormAlias);

        $this->productFormView = $productFormView;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['groups'] = $this->productFormView->getView();
    }

    /**
     * {@inheritdoc}
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
        parent::addEntityFields($builder);

        $builder->add(
            'enabled',
            'checkbox',
            array(
                'attr' => array(
                    'data-on-label'  => 'Enabled',
                    'data-off-label' => 'Disabled',
                    'size'           => null
                )
            )
        );

        $this->addLocaleField($builder);
    }

    /**
     * Add locale field
     *
     * @param FormBuilderInterface $builder
     *
     * @return ProductType
     */
    protected function addLocaleField(FormBuilderInterface $builder)
    {
        $builder->add(
            'locales',
            'entity',
            array(
                'required' => true,
                'multiple' => true,
                'class' => 'Pim\Bundle\ConfigBundle\Entity\Locale',
                'by_reference' => false,
                'query_builder' => function (EntityRepository $repository) {
                    return $repository->createQueryBuilder('l')->where('l.activated = 1')->orderBy('l.code');
                }
            )
        );
    }

    /**
     * Add entity fieldsto form builder
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function addDynamicAttributesFields(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'values',
            new LocalizedCollectionType,
            array(
                'type'               => $this->valueFormAlias,
                'allow_add'          => true,
                'allow_delete'       => true,
                'by_reference'       => false,
                'cascade_validation' => true,
                'currentLocale'      => $options['currentLocale'],
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('currentLocale' => null));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product';
    }
}
