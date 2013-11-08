<?php

namespace Oro\Bundle\SoapBundle\Entity;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttribute;

class RequestFix
{
    /**
     * @var FlexibleManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @param FlexibleManagerRegistry $managerRegistry
     */
    public function __construct(FlexibleManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * Fix Request object so forms can be handled correctly
     *
     * @param string $entityClass
     * @param array $data
     * @param string $attributeKey
     * @param string $requestAttributeKey
     * @return array
     */
    public function getFixedAttributesData(
        $entityClass,
        array $data,
        $attributeKey = 'values',
        $requestAttributeKey = 'attributes'
    ) {
        /** @var ObjectRepository $attrRepository */
        $attrRepository = $this->managerRegistry
            ->getManager($entityClass)
            ->getAttributeRepository();
        $attrDef = $attrRepository->findBy(array('entityType' => $entityClass));
        $attrVal = isset($data[$requestAttributeKey]) ? $data[$requestAttributeKey] : array();

        unset($data[$requestAttributeKey]);
        $data[$attributeKey] = array();

        foreach ($attrDef as $attr) {
            /* @var AbstractEntityAttribute $attr */
            list($type, $default) = $this->getAttributeParameters($attr);

            $attrCode = $attr->getCode();
            if ($default) {
                $data[$attributeKey][$attrCode]['id'] = $attr->getId();
                $data[$attributeKey][$attrCode][$type] = $default;
            }

            foreach ($attrVal as $fieldCode => $fieldValue) {
                if ($attr->getCode() == (string)$fieldCode) {
                    if (is_array($fieldValue)) {
                        if (array_key_exists('scope', $fieldValue)) {
                            $data[$attributeKey][$attrCode]['scope'] = $fieldValue['scope'];
                        }
                        if (array_key_exists('locale', $fieldValue)) {
                            $data[$attributeKey][$attrCode]['locale'] = $fieldValue['locale'];
                        }
                        $fieldValue = $fieldValue['value'];
                    }
                    if ($fieldValue) {
                        $data[$attributeKey][$attrCode]['id'] = $attr->getId();
                        $data[$attributeKey][$attrCode][$type] = (string)$fieldValue;
                    }

                    break;
                }
            }
        }

        return $data;
    }

    /**
     * @param AbstractEntityAttribute $attr
     * @return array
     */
    protected function getAttributeParameters(AbstractEntityAttribute $attr)
    {
        if ($attr->getBackendType() == 'options') {
            if (in_array(
                $attr->getAttributeType(),
                array(
                    'oro_flexibleentity_multiselect',
                    'oro_flexibleentity_multicheckbox',
                )
            )) {
                $type = 'options';
                $default = array($attr->getOptions()->offsetGet(0)->getId());
            } else {
                $type = 'option';
                $default = $attr->getOptions()->offsetGet(0)->getId();
            }
        } else {
            $type = $attr->getBackendType();
            //TODO: temporary fix for https://github.com/symfony/symfony/issues/8548
            $default = '';
        }

        return array($type, $default);
    }
}
