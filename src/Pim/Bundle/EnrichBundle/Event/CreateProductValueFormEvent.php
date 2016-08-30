<?php

namespace Pim\Bundle\EnrichBundle\Event;

use Pim\Component\Catalog\Model\ProductValueInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event to dynamically update each product value form
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateProductValueFormEvent extends Event
{
    /**
     * @var ProductValueInterface
     */
    protected $productValue;

    /**
     * @var string
     */
    protected $formType;

    /**
     * @var mixed
     */
    protected $formData;

    /**
     * @var array
     */
    protected $formOptions;

    /**
     * @var array
     */
    protected $context;

    /**
     * Constructor
     *
     * @param ProductValueInterface $value
     * @param string                $formType
     * @param mixed                 $formData
     * @param array                 $formOptions
     * @param array                 $context
     */
    public function __construct(ProductValueInterface $value, $formType, $formData, $formOptions, $context)
    {
        $this->productValue = $value;
        $this->formType = $formType;
        $this->formData = $formData;
        $this->formOptions = $formOptions;
        $this->context = $context;
    }

    /**
     * @return ProductValueInterface
     */
    public function getProductValue()
    {
        return $this->productValue;
    }

    /**
     * @return string
     */
    public function getFormType()
    {
        return $this->formType;
    }

    /**
     * @return mixed
     */
    public function getFormData()
    {
        return $this->formData;
    }

    /**
     * @return array
     */
    public function getFormOptions()
    {
        return $this->formOptions;
    }

    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param mixed $formType
     *
     * @return CreateProductValueFormEvent
     */
    public function updateFormType($formType)
    {
        $this->formType = $formType;

        return $this;
    }

    /**
     * @param array $formOptions
     *
     * @return CreateProductValueFormEvent
     */
    public function updateFormOptions($formOptions)
    {
        $this->formOptions = $formOptions;

        return $this;
    }

    /**
     * @param mixed $formData
     *
     * @return CreateProductValueFormEvent
     */
    public function updateFormData($formData)
    {
        $this->formData = $formData;

        return $this;
    }
}
