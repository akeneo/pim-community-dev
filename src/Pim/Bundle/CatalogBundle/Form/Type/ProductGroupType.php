<?php
namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
/**
 * Group form type
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductGroupType extends AbstractType
{

    /**
     * @var string
     */
    protected $groupClass;

    /**
     * @var string
     */
    protected $attributeClass;

    /**
     * Construct with full name of concrete impl of group class
     *
     * @param string $groupClass     the group class
     * @param string $attributeClass the attribute class
     */
    public function __construct($groupClass, $attributeClass)
    {
        $this->groupClass = $groupClass;
        $this->attributeClass = $attributeClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');
        $builder->add('code', 'hidden');
        $builder->add('title', 'hidden');
        // add group attributes
        $builder->add(
            'attributes', 'collection',
            array(
                'type'         => new ProductGroupAttributeType($this->attributeClass),
                'by_reference' => false,
                'allow_add'    => true,
                'allow_delete' => true
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => $this->groupClass
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'akeneo_productset_group';
    }
}
