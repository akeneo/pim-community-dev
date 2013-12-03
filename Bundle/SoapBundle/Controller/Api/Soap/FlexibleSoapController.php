<?php

namespace Oro\Bundle\SoapBundle\Controller\Api\Soap;

use Doctrine\Common\Util\ClassUtils;

abstract class FlexibleSoapController extends SoapController
{
    /**
     * @var string
     */
    protected $attributeKey = 'values';

    /**
     * @var string
     */
    protected $requestAttributeKey = 'attributes';

    /**
     * {@inheritDoc}
     */
    protected function fixRequestAttributes($entity)
    {
        parent::fixRequestAttributes($entity);

        $request = $this->container->get('request');
        $data = $request->request->get($this->getForm()->getName());

        // fix attributes array format to make it associative
        // and compatible with SoapBundle\Entity\FlexibleAttribute
        $values = array();
        if (isset($data[$this->requestAttributeKey])) {
            foreach ($data[$this->requestAttributeKey] as $attr) {
                $values[$attr->code] = $attr->value;
            }
            $data[$this->requestAttributeKey] = $values;
        }

        $entityClass = ClassUtils::getRealClass(get_class($entity));
        $entityClass = str_replace('Soap', '', $entityClass);

        $data = $this->container->get('oro_soap.request')->getFixedAttributesData(
            $entityClass,
            $data,
            $this->attributeKey,
            $this->requestAttributeKey
        );
        $request->request->set($this->getForm()->getName(), $data);
    }
}
