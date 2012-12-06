<?php
namespace Pim\Bundle\CatalogBundle\Form\Type;

use Oro\Bundle\FlexibleEntityBundle\Model\EntityAttribute;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Strixos\CatalogBundle\Entity\Attribute;

/**
 * Aims to use collection of attribute link to pick them in set form
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductGroupAttributeType extends AbstractType
{
    /**
     * Used to populate from the constructor
     * @param Attribute
     */
    private $attribute = null;

    /**
     * Construct
     *
     * @param string          $attributeClass attribute class
     * @param EntityAttribute $attribute      attribut instance
     */
    public function __construct($attributeClass, EntityAttribute $attribute = null)
    {
        $this->attributeClass = $attributeClass;
        if ($attribute) {
            $this->attribute = $attribute;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');
        $builder->add('code');
        $builder->add('title', 'hidden');
        if (!is_null($this->attribute)) {
            $builder->setData($this->attribute);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => $this->attributeClass,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'akeneo_productgroup_attribute';
    }

}
