<?php
namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pim\Bundle\CatalogBundle\Model\BaseFieldFactory;

/**
 * Type for product form (independant of persistence)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductType extends AbstractType
{
    /**
     * @var string
     */
    protected $productClass;

    /**
     * Construct with full name of concrete impl of product class
     * @param unknown_type $productClass
     */
    public function __construct($productClass)
    {
        $this->productClass = $productClass;
    }

    /**
     * Build form
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $options['data'];

        // TODO drive from type and not add if in twig template ?
        $builder->add('id', 'hidden');

        // add product field
        if ($entity->getSet()) {
            foreach ($entity->getSet()->getGroups() as $group) {
                foreach ($group->getAttributes() as $attribute) {

                    // TODO required, scope etc

                    // TODO filter values not efficient
                    $values = $entity->getValues()->filter(function($value) use ($attribute) {
                        return $value->getAttribute() == $attribute;
                    });
                    $value = $values->first();

                    // prepare common fields options
                    $customOptions = array(
                        'label'         => $attribute->getTitle(),
                        'data'          => ($value) ? $value->getData() : '',
                        'by_reference'  => false,
                        'property_path' => false,
                        'required'      => ($attribute->getValueRequired() == 1)
                    );

                    // add text fields options
                    if ($attribute->getType() == BaseFieldFactory::FIELD_STRING) {
                        $attributeType = 'text';

                    // add select field options
                    } else if ($attribute->getType() == BaseFieldFactory::FIELD_SELECT) {
                        $attributeType = 'choice';
                        $options = $attribute->getOptions();
                        $choices = array();
                        // TODO option order
                        foreach ($options as $option) {
                            $choices[$option->getId()]= $option->getValue();
                        }
                        $customOptions['choices']= $choices;
                        $customOptions['data']= ($value) ? $value->getData() : '';
                    }

                    // add field
                    $builder->add($attribute->getCode(), $attributeType, $customOptions);
                }
            }
        }

    }

    /**
     * Setup default options
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => $this->productClass
            )
        );
    }

    /**
     * Get identifier
     * @return string
     */
    public function getName()
    {
        return 'pim_catalogbundle_product';
    }
}
