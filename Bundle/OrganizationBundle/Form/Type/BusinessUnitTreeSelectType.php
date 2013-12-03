<?php
namespace Oro\Bundle\OrganizationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\OrganizationBundle\Form\Transformer\BusinessUnitTransformer;

class BusinessUnitTreeSelectType extends AbstractType
{
    /**
     * @var BusinessUnitTransformer
     */
    protected $transformer;

    public function __construct(BusinessUnitTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function getParent()
    {
        return 'choice';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->addModelTransformer($this->transformer);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'configs' => array()
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_business_unit_tree_select';
    }
}
