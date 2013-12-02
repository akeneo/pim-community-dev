<?php

namespace Oro\Bundle\SoapBundle\Controller\Api\Rest;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Proxy\Proxy;

use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\ScopableInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\TranslatableInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;

abstract class FlexibleRestController extends RestController
{
    /**
     * {@inheritDoc}
     */
    protected function getPreparedItem($entity)
    {
        $result = parent::getPreparedItem($entity);
        if (array_key_exists('values', $result)) {
            $result['attributes'] = $result['values'];
            unset($result['values']);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    protected function transformEntityField($field, &$value)
    {
        if ($field == 'values') {
            $flexibleValues = $value;
            $value = array();
            /** @var FlexibleValueInterface $flexibleValue */
            foreach ($flexibleValues as $flexibleValue) {
                if ($flexibleValue instanceof Proxy) {
                    /** @var Proxy $flexibleValue */
                    $flexibleValue->__load();
                }
                $attributeValue = $flexibleValue->getData();
                if ($attributeValue) {
                    /** @var Attribute $attribute */
                    $attribute = $flexibleValue->getAttribute();
                    parent::transformEntityField($attribute->getCode(), $attributeValue);
                    $attributeData = array('value' => $attributeValue);
                    if ($attributeValue instanceof TranslatableInterface) {
                        /** @var TranslatableInterface $flexibleValue */
                        $attributeData['locale'] = $flexibleValue->getLocale();
                    }
                    if ($attributeValue instanceof ScopableInterface) {
                        /** @var ScopableInterface $flexibleValue */
                        $attributeData['scope'] = $flexibleValue->getScope();
                    }
                    $value[$attribute->getCode()] = (object) $attributeData;
                }
            }
        } else {
            parent::transformEntityField($field, $value);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function processForm($entity)
    {
        $this->fixRequestAttributes($entity);

        return parent::processForm($entity);
    }

    /**
     * Transform request
     *
     * Assumed post data in the following format:
     * {entity: {"id": "21", "property_one": "Test", "attributes": {"flexible_attribute_code": "John"}}}
     * {entity: {"id": "21", "property_one": "Test", "attributes": {"flexible_attribute_code": {"value": "John", "scope": "mobile"}}}}
     *
     * @param mixed $entity
     */
    protected function fixRequestAttributes($entity)
    {
        $requestVariable = $this->getForm()->getName();
        $request = $this->getRequest()->request;
        $data = $request->get($requestVariable, array());

        $entityClass = ClassUtils::getRealClass(get_class($entity));
        $data = $this->container->get('oro_soap.request')->getFixedAttributesData($entityClass, $data);
        $request->set($requestVariable, $data);
    }
}
