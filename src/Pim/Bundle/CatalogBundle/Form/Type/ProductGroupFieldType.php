<?php
namespace Pim\Bundle\CatalogBundle\Form\Type;

use Bap\Bundle\FlexibleEntityBundle\Model\EntityField;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Strixos\CatalogBundle\Entity\Attribute;

/**
 * Aims to use collection of attribute link to pick them in set form
 *
 * @author     Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductGroupFieldType extends AbstractType
{
    /**
     * Used to populate from the constructor
     * @param Attribute
     */
    private $field = null;

    /**
     * Construct
     */
    public function __construct(EntityField $field = null)
    {
        if ($field) {
            $this->field = $field;
        }
    }

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Form.AbstractType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//         $entity = $options['data'];
        
        $builder->add('id', 'hidden');
        $builder->add('code');
        if (!is_null($this->field)) {
            $builder->setData($this->field);
        }
    }

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Form.AbstractType::getDefaultOptions()
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Bap\Bundle\FlexibleEntityBundle\Model\EntityField',
        );
    }

    /**
     * Return identifier
     * @see Symfony\Component\Form.FormTypeInterface::getName()
     */
    public function getName()
    {
        return 'akeneo_producttype_field';
    }

}