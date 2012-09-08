<?php
namespace Strixos\CatalogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Strixos\CatalogBundle\Entity\Attribute;

/**
 * Aims to display product values
 * @author     Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ValueType extends AbstractType
{
    /**
     * Used to populate from the constructor
     * @param Attribute
     */
    private $_attribute = null;

    /**
     * Used to populate from the constructor
     * @param mixed
     */
    private $_value = null;

    /**
     * Construct
     */
    public function __construct($attribute = null, $value = null)
    {
        if ($attribute) {
            $this->_attribute = $attribute;
        }
        if ($value) {
            $this->_value = $value;
        }
    }

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Form.AbstractType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // determine input
        switch ($this->_attribute->getInput()) {
            case Attribute::FRONTEND_INPUT_TEXTAREA:
                $input = 'textarea';
                break;
            /*case Attribute::FRONTEND_INPUT_DATE:
                $input = 'date';
                break;*/
            case Attribute::FRONTEND_INPUT_SELECT:
                $input = 'choice';
                break;
            default :
                $input = 'text';
        }
        // add specific fields options
        $isRequired = $this->_attribute->getIsRequired();
        $fieldOptions = array(
            'required' => $isRequired,
            'label'    => $this->_attribute->getCode(),
            'data'     => $this->_value,
        );
        if ($input == 'choice') {
            $fieldOptions['choices'] = $this->_attribute->getOptionsArray();
        }
        // add field
        $builder->add('attribute', $input, $fieldOptions);
    }

    /**
     * Return identifier
     * @see Symfony\Component\Form.FormTypeInterface::getName()
     */
    public function getName()
    {
        return 'strixos_catalog_product_value';
    }

}