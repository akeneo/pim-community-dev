<?php
namespace Oro\Bundle\FlexibleEntityBundle\AttributeType;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\Form\FormFactoryInterface;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * Abstract attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
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
    const BACKEND_TYPE_DATE          = 'date';
    const BACKEND_TYPE_DATETIME      = 'datetime';
    const BACKEND_TYPE_DECIMAL       = 'decimal';
    const BACKEND_TYPE_INTEGER       = 'integer';
    const BACKEND_TYPE_OPTIONS       = 'options';
    const BACKEND_TYPE_OPTION        = 'option';
    const BACKEND_TYPE_TEXT          = 'text';
    const BACKEND_TYPE_VARCHAR       = 'varchar';
    const BACKEND_TYPE_MEDIA         = 'media';
    const BACKEND_TYPE_METRIC        = 'metric';
    const BACKEND_TYPE_PRICE         = 'price';
    const BACKEND_TYPE_COLLECTION    = 'collections';

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
     * @param string $backendType the backend type
     * @param string $formType    the form type
     */
    public function __construct($backendType, $formType)
    {
        $this->backendType = $backendType;
        $this->formType    = $formType;
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
        $options = $this->prepareValueFormOptions($value);
        $data    = $this->prepareValueFormData($value);
        $form    = $factory->createNamed($name, $type, $data, $options);

        return $form;
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
        $options = array(
            'label'       => $value->getAttribute()->getLabel(),
            'required'    => $value->getAttribute()->getRequired(),
            'constraints' => array()
        );

        if ($options['required']) {
            $options['constraints'][] = new Constraints\NotBlank();
        }

        switch ($value->getAttribute()->getBackendType()) {
            case self::BACKEND_TYPE_DATE:
                $options['constraints'][] = new Constraints\Date();
                break;
            case self::BACKEND_TYPE_DATE:
                $options['constraints'][] = new Constraints\Date();
                break;
            case self::BACKEND_TYPE_DATETIME:
                $options['constraints'][] = new Constraints\DateTime();
                break;
        }

        return $options;
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
        // TODO will be used to build attribute create / edit form for attribute management, cf BAP-650

        return null;
    }
}
