<?php

namespace Pim\Bundle\FlexibleEntityBundle\AttributeType;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\Form\FormFactoryInterface;
use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface;

/**
 * Abstract attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractAttributeType implements AttributeTypeInterface
{
    /**
     * Available backend storage, the flexible doctrine mapped field
     *
     * @var string
     */
    const BACKEND_STORAGE_ATTRIBUTE_VALUE = 'values';

    /**
     * Available backend types, the doctrine mapped field in value class
     *
     * @var string
     */
    const BACKEND_TYPE_DATE       = 'date';
    const BACKEND_TYPE_DATETIME   = 'datetime';
    const BACKEND_TYPE_DECIMAL    = 'decimal';
    const BACKEND_TYPE_BOOLEAN    = 'boolean';
    const BACKEND_TYPE_INTEGER    = 'integer';
    const BACKEND_TYPE_OPTIONS    = 'options';
    const BACKEND_TYPE_OPTION     = 'option';
    const BACKEND_TYPE_TEXT       = 'text';
    const BACKEND_TYPE_VARCHAR    = 'varchar';
    const BACKEND_TYPE_MEDIA      = 'media';
    const BACKEND_TYPE_METRIC     = 'metric';
    const BACKEND_TYPE_PRICE      = 'price';
    const BACKEND_TYPE_COLLECTION = 'collections';
    const BACKEND_TYPE_ENTITY     = 'entity';

    /**
     * Field backend type, "varchar" by default, the doctrine mapping field, getter / setter to use for binding
     *
     * @var string
     */
    protected $backendType = self::BACKEND_TYPE_VARCHAR;

    /**
     * Form type alias, "text" by default
     *
     * @var string
     */
    protected $formType = 'text';

    /**
     * Constructor
     *
     * @param string                     $backendType       the backend type
     * @param string                     $formType          the form type
     * @param ConstraintGuesserInterface $constraintGuesser the form type
     */
    public function __construct($backendType, $formType, ConstraintGuesserInterface $constraintGuesser)
    {
        $this->backendType       = $backendType;
        $this->formType          = $formType;
        $this->constraintGuesser = $constraintGuesser;
    }

    /**
     * Get backend type
     *
     * @return string
     */
    public function getBackendType()
    {
        return $this->backendType;
    }

    /**
     * Get form type (alias)
     *
     * @return string
     */
    public function getFormType()
    {
        return $this->formType;
    }

    /**
     * {@inheritdoc}
     */
    public function buildValueFormType(FormFactoryInterface $factory, FlexibleValueInterface $value)
    {
        $name    = $this->prepareValueFormName($value);
        $type    = $this->prepareValueFormAlias($value);
        $data    = $this->prepareValueFormData($value);
        $options = array_merge(
            $this->prepareValueFormConstraints($value),
            $this->prepareValueFormOptions($value)
        );

        return $factory->createNamed($name, $type, $data, $options);
    }

    /**
     * Get the value form type name to use to ensure binding
     *
     * @param FlexibleValueInterface $value
     *
     * @return string
     */
    protected function prepareValueFormName(FlexibleValueInterface $value)
    {
        return $value->getAttribute()->getBackendType();
    }

    /**
     * Get value form type alias to use to render value
     *
     * @param FlexibleValueInterface $value
     *
     * @return string
     */
    protected function prepareValueFormAlias(FlexibleValueInterface $value)
    {
        return $this->getFormType();
    }

    /**
     * Get value form type options to configure the form
     *
     * @param FlexibleValueInterface $value
     *
     * @return array
     */
    protected function prepareValueFormOptions(FlexibleValueInterface $value)
    {
        return array(
            'label'           => $value->getAttribute()->getLabel(),
            'required'        => $value->getAttribute()->isRequired(),
            'auto_initialize' => false
        );
    }

    /**
     * Guess the constraints to apply on the form
     *
     * @param FlexibleValueInterface $value
     *
     * @return multitype:NULL |multitype:
     */
    protected function prepareValueFormConstraints(FlexibleValueInterface $value)
    {
        if ($this->constraintGuesser->supportAttribute($attribute = $value->getAttribute())) {
            return array(
                'constraints' => $this->constraintGuesser->guessConstraints($attribute),
            );
        }

        return array();
    }

    /**
     * Get value form type data
     *
     * @param FlexibleValueInterface $value
     *
     * @return mixed
     */
    protected function prepareValueFormData(FlexibleValueInterface $value)
    {
        return is_null($value->getData()) ? $value->getAttribute()->getDefaultValue() : $value->getData();
    }

    /**
     * {@inheritdoc}
     */
    public function buildAttributeFormTypes(FormFactoryInterface $factory, AbstractAttribute $attribute)
    {
        $properties = $this->defineCustomAttributeProperties($attribute);

        $types = array();

        foreach ($properties as $property) {
            $fieldType = 'text';
            if (isset($property['fieldType'])) {
                $fieldType = $property['fieldType'];
            }
            $data = null;
            if (isset($property['data'])) {
                $data = $property['data'];
            }
            $options = array();
            if (isset($property['options'])) {
                $options = $property['options'];
            }
            if (!isset($options['required'])) {
                $options['required'] = false;
            }
            $options['auto_initialize'] = false;

            $types[] = $factory->createNamed($property['name'], $fieldType, $data, $options);
        }

        return $types;
    }

    /**
     * Define custom properties used in attribute form
     *
     * Each property must be an array with a 'name' key that matches the name of the property
     * Optional 'fieldType', 'data' and 'options' keys can be provided for field customization
     *
     * @param AbstractAttribute $attribute Attribute entity
     *
     * @return array:array:multitype $properties an array of custom properties
     */
    protected function defineCustomAttributeProperties(AbstractAttribute $attribute)
    {
        return array();
    }
}
