<?php

namespace Pim\Bundle\EnrichBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

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
     * Constructor
     *
     * @param ProductValueInterface $value
     * @param string                $formType
     * @param mixed                 $formData
     * @param array                 $formOptions
     */
    public function __construct(ProductValueInterface $value, $formType, $formData, $formOptions)
    {
        $this->productValue = $value;
        $this->formType     = $formType;
        $this->formData     = $formData;
        $this->formOptions  = $formOptions;
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
